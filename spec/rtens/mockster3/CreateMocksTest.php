<?php
namespace spec\rtens\mockster3;

use rtens\mockster3\Mockster;
use watoki\factory\Factory;
use watoki\scrut\Specification;

class CreateMocksTest extends Specification {

    /** @var CreateMocksTest_FooClass|Mockster */
    private $foo;

    /** @var CreateMocksTest_FooClass */
    private $mock;

    protected function setUp() {
        parent::setUp();
        $this->foo = new Mockster(CreateMocksTest_FooClass::$class);
    }

    function testPlainMock() {
        $this->mock = $this->foo->mock();
        $this->assertFalse($this->mock->constructorCalled);
        $this->assertTrue(Mockster::stub($this->foo->foo())->isStubbed());
    }

    function testMockAbstractClass() {
        $foo = new Mockster(CreateMocksTest_AbstractClass::$class);
        $this->assertInstanceOf(CreateMocksTest_AbstractClass::$class, $foo->mock());
    }

    function testMockInterface() {
        $foo = new Mockster(CreateMocksTest_Interface::CreateMocksTest_Interface);
        $this->assertInstanceOf(CreateMocksTest_Interface::CreateMocksTest_Interface, $foo->mock());
    }

    function testUnitUnderTest() {
        $this->mock = $this->foo->uut();
        $this->assertTrue($this->mock->constructorCalled);
        $this->assertFalse(Mockster::stub($this->foo->foo())->isStubbed());
    }

    function testPassConstructorArgumentsToUut() {
        $this->mock = $this->foo->uut(['uno', 'dos']);
        $this->assertEquals(['uno', 'dos'], $this->mock->constructorArguments);
    }

    function testPassConstructorArgumentsByName() {
        $this->mock = $this->foo->uut(['one' => 'uno', 'two' => 'dos']);
        $this->assertEquals(['uno', 'dos'], $this->mock->constructorArguments);
    }

    function testMockInjectableConstructorArguments() {
        /** @var Mockster|CreateMocksTest_InjectableClass $injectable */
        $injectable = new Mockster(CreateMocksTest_InjectableClass::$class);
        /** @var CreateMocksTest_InjectableClass $mock */
        $mock = $injectable->uut();

        $this->assertInstanceOf(CreateMocksTest_FooClass::$class, $mock->foo);
    }

    function testMockInjectableProperties() {
        /** @var Mockster|CreateMocksTest_InjectableClass $injectable */
        $injectable = new Mockster(CreateMocksTest_InjectableClass::$class);
        /** @var CreateMocksTest_InjectableClass $mock */
        $mock = $injectable->uut();

        $this->assertInstanceOf(CreateMocksTest_FooClass::$class, $mock->bar);
    }

    function testNotExistingProperty() {
        $injectable = new Mockster(CreateMocksTest_InjectableClass::$class);

        try {
            /** @noinspection PhpUndefinedFieldInspection */
            $injectable->notExisting;
            $this->fail("Should have thrown an Exception");
        } catch (\ReflectionException $e) {
            $this->assertContains("InjectableClass::notExisting", $e->getMessage());
        }
    }

    function testMocksDoNotInjectProperties() {
        /** @var Mockster|CreateMocksTest_InjectableClass $injectable */
        $injectable = new Mockster(CreateMocksTest_InjectableClass::$class);
        /** @var CreateMocksTest_InjectableClass $mock */
        $mock = $injectable->mock();

        $this->assertNull($mock->bar);
        $this->assertInstanceOf(get_class($injectable), $injectable->bar);
    }

    function testStubMethodsOfInjectedMocks() {
        /** @var Mockster|CreateMocksTest_InjectableClass $injectable */
        $injectable = new Mockster(CreateMocksTest_InjectableClass::$class);
        /** @var CreateMocksTest_InjectableClass $mock */
        $mock = $injectable->uut();

        Mockster::stub($injectable->bar->foo())->will()->return_('foo');
        $this->assertEquals('foo', $mock->bar->foo());
    }

    function testInjectFactory() {
        $singleton = new \DateTime();

        $factory = new Factory();
        $factory->setSingleton($singleton, CreateMocksTest_FooClass::$class);

        /** @var CreateMocksTest_InjectableClass $mock */
        $mock = (new Mockster(CreateMocksTest_InjectableClass::$class, $factory))->uut();

        $this->assertEquals($singleton, $mock->bar);
    }

    function testForceParameterCount() {
        /** @var Mockster|CreateMocksTest_Methods $methods */
        $methods = new Mockster(CreateMocksTest_Methods::$class);
        $mock = $methods->mock();

        try {
            $mock->twoParameters('one');
            $this->fail("Should have thrown an exception");
        } catch (\Exception $e) {
            $this->assertContains("Missing argument 2", $e->getMessage());
        }
    }

    function testKeepArrayTypeHint() {
        /** @var Mockster|CreateMocksTest_Methods $methods */
        $methods = new Mockster(CreateMocksTest_Methods::$class);
        /** @var CreateMocksTest_Methods $mock */
        $mock = $methods->mock();

        try {
            $mock->arrayHint('one');
            $this->fail("Should have thrown an exception");
        } catch (\Exception $e) {
            $this->assertContains("must be of the type array", $e->getMessage());
        }
    }

    function testKeepCallableTypeHint() {
        /** @var Mockster|CreateMocksTest_Methods $methods */
        $methods = new Mockster(CreateMocksTest_Methods::$class);
        /** @var CreateMocksTest_Methods $mock */
        $mock = $methods->mock();

        try {
            $mock->callableHint('one');
            $this->fail("Should have thrown an exception");
        } catch (\Exception $e) {
            $this->assertContains("must be callable", $e->getMessage());
        }
    }

    function testKeepClassTypeHint() {
        /** @var Mockster|CreateMocksTest_Methods $methods */
        $methods = new Mockster(CreateMocksTest_Methods::$class);
        /** @var CreateMocksTest_Methods $mock */
        $mock = $methods->mock();

        try {
            /** @noinspection PhpParamsInspection */
            $mock->classHint('one');
            $this->fail("Should have thrown an exception");
        } catch (\Exception $e) {
            $this->assertContains("must be an instance of DateTime", $e->getMessage());
        }
    }

    function testKeepVariadicMethod() {
        if (PHP_VERSION_ID < 50600) {
            $this->markTestSkipped('Only in PHP >= 5.6');
        }

        eval('
            class CreateMocksTest_VariadicMethod {
                public function variadic(...$a) {
                    return $a;
                }
            }
        ');

        /** @var object|Mockster $methods */
        $methods = new Mockster('CreateMocksTest_VariadicMethod');
        /** @var object $mock */
        $mock = $methods->mock();

        Mockster::stub($methods->variadic('one', 'two'))->will()->call(function ($args) {
            return json_encode($args);
        });

        $this->assertContains('"0":"one","1":"two"', $mock->variadic('one', 'two'));
    }
}

class CreateMocksTest_FooClass {
    public static $class = __CLASS__;

    public $constructorCalled = false;
    public $constructorArguments = null;

    function __construct($one = null, $two = null) {
        $this->constructorCalled = true;
        $this->constructorArguments = [$one, $two];
    }

    /**
     * @return string
     */
    function foo() {
        return 'bar';
    }
}

abstract class CreateMocksTest_AbstractClass {
    public static $class = __CLASS__;
}

interface CreateMocksTest_Interface {
    const CreateMocksTest_Interface = __CLASS__;
}

class CreateMocksTest_InjectableClass {
    public static $class = __CLASS__;

    /** @var CreateMocksTest_FooClass */
    public $bar;

    /**
     * @param CreateMocksTest_FooClass $foo <-
     */
    function __construct($foo) {
        $this->foo = $foo;
    }
}

class CreateMocksTest_Methods {
    public static $class = __CLASS__;

    public function twoParameters($a, $b) {
    }

    public function arrayHint(array $array) {
    }

    public function callableHint(callable $callable) {
    }

    public function classHint(\DateTime $date) {
    }
}