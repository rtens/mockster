<?php
namespace spec\rtens\mockster;

use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;

class CreateMocksTest extends StaticTestSuite {

    /** @var CreateMocksTest_FooClass|Mockster */
    private $foo;

    /** @var CreateMocksTest_FooClass */
    private $mock;

    public function before() {
        $this->foo = new Mockster(CreateMocksTest_FooClass::class);
    }

    function testPlainMock() {
        $this->mock = $this->foo->__mock();
        $this->assert->not($this->mock->constructorCalled);
        $this->assert(Mockster::stub($this->foo->foo())->isStubbed());
    }

    function testMockAbstractClass() {
        $foo = new Mockster(CreateMocksTest_AbstractClass::class);
        $this->assert->isInstanceOf($foo->__mock(), CreateMocksTest_AbstractClass::class);
    }

    function testMockInterface() {
        $foo = new Mockster(CreateMocksTest_Interface::class);
        $this->assert->isInstanceOf($foo->__mock(), CreateMocksTest_Interface::class);
    }

    function testUnitUnderTest() {
        $this->mock = $this->foo->__uut();
        $this->assert($this->mock->constructorCalled);
        $this->assert->not(Mockster::stub($this->foo->foo())->isStubbed());
    }

    function testPassConstructorArgumentsToUut() {
        $this->mock = $this->foo->__uut(['uno', 'dos']);
        $this->assert($this->mock->constructorArguments, ['uno', 'dos']);
    }

    function testPassConstructorArgumentsByName() {
        $this->mock = $this->foo->__uut(['one' => 'uno', 'two' => 'dos']);
        $this->assert($this->mock->constructorArguments, ['uno', 'dos']);
    }

    function testForceParameterCount() {
        /** @var Mockster|CreateMocksTest_Methods $methods */
        $methods = new Mockster(CreateMocksTest_Methods::class);
        $mock = $methods->__mock();

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
        $mock = $methods->__mock();

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
        $mock = $methods->__mock();

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
        $mock = $methods->__mock();

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
        $mock = $methods->__mock();

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