<?php
namespace spec\rtens\mockster3;

use rtens\mockster3\Mockster;
use watoki\scrut\Specification;

class CreateMocksTest extends Specification {

    /** @var CreateMocksTest_FooClass|Mockster */
    private $foo;

    /** @var CreateMocksTest_FooClass */
    private $mock;

    protected function setUp() {
        parent::setUp();
        $this->foo = new Mockster(CreateMocksTest_FooClass::class);
    }

    function testPlainMock() {
        $this->mock = $this->foo->mock();
        $this->assertFalse($this->mock->constructorCalled);
        $this->assertTrue(Mockster::stub($this->foo->foo())->isStubbed());
    }

    function testMockAbstractClass() {
        $foo = new Mockster(CreateMocksTest_AbstractClass::class);
        $this->assertInstanceOf(CreateMocksTest_AbstractClass::class, $foo->mock());
    }

    function testMockInterface() {
        $foo = new Mockster(CreateMocksTest_Interface::class);
        $this->assertInstanceOf(CreateMocksTest_Interface::class, $foo->mock());
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
        $injectable = new Mockster(CreateMocksTest_InjectableClass::class);
        /** @var CreateMocksTest_InjectableClass $mock */
        $mock = $injectable->uut();

        $this->assertInstanceOf(CreateMocksTest_FooClass::class, $mock->foo);
    }

    function testForceParameterCount() {
        /** @var Mockster|CreateMocksTest_Methods $methods */
        $methods = new Mockster(CreateMocksTest_Methods::class);
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
        $methods = new Mockster(CreateMocksTest_Methods::class);
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
        $methods = new Mockster(CreateMocksTest_Methods::class);
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
        $methods = new Mockster(CreateMocksTest_Methods::class);
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
        /** @var Mockster|CreateMocksTest_Methods $methods */
        $methods = new Mockster(CreateMocksTest_Methods::class);
        /** @var CreateMocksTest_Methods $mock */
        $mock = $methods->mock();

        $this->assertEquals('one two', $mock->variadic('one', 'two'));
    }
}

class CreateMocksTest_FooClass {
    public $constructorCalled = false;
    public $constructorArguments = null;

    function __construct($one = null, $two = null) {
        $this->constructorCalled = true;
        $this->constructorArguments = [$one, $two];
    }

    function foo() {
        return 'bar';
    }
}

abstract class CreateMocksTest_AbstractClass {
}

interface CreateMocksTest_Interface {
}

class CreateMocksTest_InjectableClass {

    /** @var CreateMocksTest_FooClass */
    public $foo;

    /**
     * @param CreateMocksTest_FooClass $foo <-
     */
    function __construct($foo) {
        $this->foo = $foo;
    }
}

class CreateMocksTest_Methods {
    public function twoParameters($a, $b) {
    }

    public function arrayHint(array $array) {
    }

    public function callableHint(callable $callable) {
    }

    public function classHint(\DateTime $date) {
    }

    public function variadic(...$a) {
        return implode(' ', $a);
    }
}