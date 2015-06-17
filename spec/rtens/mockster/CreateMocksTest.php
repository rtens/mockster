<?php
namespace spec\rtens\mockster;

use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\factory\Factory;

class CreateMocksTest extends StaticTestSuite {

    /** @var CreateMocksTest_FooClass|Mockster */
    private $foo;

    /** @var CreateMocksTest_FooClass */
    private $mock;

    public function before() {
        $this->foo = new Mockster(CreateMocksTest_FooClass::class);
    }

    function testPlainMock() {
        $this->mock = $this->foo->mock();
        $this->assert->not($this->mock->constructorCalled);
        $this->assert(Mockster::stub($this->foo->foo())->isStubbed());
    }

    function testMockAbstractClass() {
        $foo = new Mockster(CreateMocksTest_AbstractClass::class);
        $this->assert->isInstanceOf($foo->mock(), CreateMocksTest_AbstractClass::class);
    }

    function testMockInterface() {
        $foo = new Mockster(CreateMocksTest_Interface::class);
        $this->assert->isInstanceOf($foo->mock(), CreateMocksTest_Interface::class);
    }

    function testUnitUnderTest() {
        $this->mock = $this->foo->uut();
        $this->assert($this->mock->constructorCalled);
        $this->assert->not(Mockster::stub($this->foo->foo())->isStubbed());
    }

    function testPassConstructorArgumentsToUut() {
        $this->mock = $this->foo->uut(['uno', 'dos']);
        $this->assert($this->mock->constructorArguments, ['uno', 'dos']);
    }

    function testPassConstructorArgumentsByName() {
        $this->mock = $this->foo->uut(['one' => 'uno', 'two' => 'dos']);
        $this->assert($this->mock->constructorArguments, ['uno', 'dos']);
    }

    function testMockInjectableConstructorArguments() {
        /** @var CreateMocksTest_InjectableClass $mock */
        $mock = (new Mockster(CreateMocksTest_InjectableClass::class))->uut();

        $mock->foo->foo();

        $this->assert->isInstanceOf($mock->foo, CreateMocksTest_FooClass::class);
        $this->assert->not($mock->foo->constructorCalled);
    }

    function testMockInjectableProperties() {
        /** @var CreateMocksTest_InjectableClass $mock */
        $mock = (new Mockster(CreateMocksTest_InjectableClass::class))->uut();

        $mock->bar->foo();

        $this->assert->isInstanceOf($mock->bar, CreateMocksTest_FooClass::class);
    }

    function testNotExistingProperty() {
        $injectable = new Mockster(CreateMocksTest_InjectableClass::class);

        try {
            /** @noinspection PhpUndefinedFieldInspection */
            $injectable->notExisting;
            $this->fail("Should have thrown an Exception");
        } catch (\ReflectionException $e) {
            $this->assert->contains($e->getMessage(), "InjectableClass::notExisting");
        }
    }

    function testMocksDoNotInjectProperties() {
        /** @var Mockster|CreateMocksTest_InjectableClass $injectable */
        $injectable = new Mockster(CreateMocksTest_InjectableClass::class);
        /** @var CreateMocksTest_InjectableClass $mock */
        $mock = $injectable->mock();

        $this->assert($mock->bar === null);
        $this->assert->isInstanceOf($injectable->bar, Mockster::class);
    }

    function testStubMethodsOfPropertyInjectedMocks() {
        /** @var Mockster|CreateMocksTest_InjectableClass $injectable */
        $injectable = new Mockster(CreateMocksTest_InjectableClass::class);
        /** @var CreateMocksTest_InjectableClass $mock */
        $mock = $injectable->uut();

        Mockster::stub($injectable->bar->foo())->will()->return_('foo');
        $this->assert($mock->bar->foo(), 'foo');
    }

    function testStubMethodsOfConstructorInjectedMocks() {
        /** @var Mockster|CreateMocksTest_InjectableClass $injectable */
        $injectable = new Mockster(CreateMocksTest_InjectableClass::class);
        /** @var CreateMocksTest_InjectableClass $mock */
        $mock = $injectable->uut();

        Mockster::stub($injectable->bas->foo())->will()->return_('foo');
        $this->assert($mock->bas->foo(), 'foo');
    }

    function testInjectFactory() {
        $factory = new Factory();
        $factory->setSingleton(new \DateTime(), CreateMocksTest_FooClass::class);

        /** @var CreateMocksTest_InjectableClass $mock */
        $mock = (new Mockster(CreateMocksTest_InjectableClass::class, $factory))->uut();

        $this->assert($mock->bar, new \DateTime());
    }

    function testForceParameterCount() {
        /** @var Mockster|CreateMocksTest_Methods $methods */
        $methods = new Mockster(CreateMocksTest_Methods::class);
        $mock = $methods->mock();

        try {
            $mock->twoParameters('one');
            $this->fail("Should have thrown an exception");
        } catch (\Exception $e) {
            $this->assert->contains($e->getMessage(), "Missing argument 2");
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
            $this->assert->contains($e->getMessage(), "must be of the type array");
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
            $this->assert->contains($e->getMessage(), "must be callable");
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
            $this->assert->contains($e->getMessage(), "must be an instance of DateTime");
        }
    }

    function testKeepVariadicMethod() {
        /** @var Mockster|CreateMocksTest_Methods $methods */
        $methods = new Mockster(CreateMocksTest_Methods::class);
        /** @var CreateMocksTest_Methods $mock */
        $mock = $methods->mock();

        Mockster::stub($methods->variadic('one', 'two'))->will()->call(function ($args) {
            return json_encode($args);
        });

        $this->assert->contains($mock->variadic('one', 'two'), '"0":"one","1":"two"');
    }
}

class CreateMocksTest_FooClass {
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
}

interface CreateMocksTest_Interface {
}

class CreateMocksTest_InjectableClass {

    /** @var CreateMocksTest_FooClass */
    public $bar;

    /** @var CreateMocksTest_FooClass */
    public $bas;

    /**
     * @param CreateMocksTest_FooClass $foo <-
     * @param CreateMocksTest_FooClass $bas <-
     */
    function __construct($foo, $bas) {
        $this->foo = $foo;
        $this->bas = $bas;
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
        return $a;
    }
}