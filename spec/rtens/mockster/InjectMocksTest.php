<?php
namespace spec\rtens\mockster;

use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\factory\Factory;

class InjectMocksTest extends StaticTestSuite {

    function testMockInjectableConstructorArguments() {
        /** @var InjectMocksTest_InjectableClass $mock */
        $mock = (new Mockster(InjectMocksTest_InjectableClass::class))->uut();

        $this->assert->isInstanceOf($mock->foo, InjectMocksTest_FooClass::class);
        $this->assert->not($mock->foo->constructorCalled);
        $mock->foo->foo();
    }

    function testMockInjectableProperties() {
        /** @var InjectMocksTest_InjectableClass $mock */
        $mock = (new Mockster(InjectMocksTest_InjectableClass::class))->uut();

        $this->assert->isInstanceOf($mock->bar, InjectMocksTest_FooClass::class);
        $mock->bar->foo();

        $this->assert->isInstanceOf($mock->multi, InjectMocksTest_FooClass::class);
        $this->assert->isInstanceOf($mock->nullable, InjectMocksTest_FooClass::class);
        $this->assert->isNull($mock->invalid);
    }

    function testNotExistingProperty() {
        $injectable = new Mockster(InjectMocksTest_InjectableClass::class);

        try {
            /** @noinspection PhpUndefinedFieldInspection */
            $injectable->notExisting;
            $this->fail("Should have thrown an Exception");
        } catch (\ReflectionException $e) {
            $this->assert->contains($e->getMessage(), "InjectableClass::notExisting");
        }
    }

    function testMocksDoNotInjectProperties() {
        /** @var Mockster|InjectMocksTest_InjectableClass $injectable */
        $injectable = new Mockster(InjectMocksTest_InjectableClass::class);
        /** @var InjectMocksTest_InjectableClass $mock */
        $mock = $injectable->mock();

        $this->assert($mock->bar === null);
        $this->assert->isInstanceOf($injectable->bar, Mockster::class);
    }

    function testStubMethodsOfPropertyInjectedMocks() {
        /** @var Mockster|InjectMocksTest_InjectableClass $injectable */
        $injectable = new Mockster(InjectMocksTest_InjectableClass::class);
        /** @var InjectMocksTest_InjectableClass $mock */
        $mock = $injectable->uut();

        Mockster::stub($injectable->bar->foo())->will()->return_('foo');
        $this->assert($mock->bar->foo(), 'foo');
    }

    function testFailWhenAccessingANonInjectableProperty() {
        /** @var Mockster|InjectMocksTest_InjectableClass $injectable */
        $injectable = new Mockster(InjectMocksTest_InjectableClass::class);

        try {
            $injectable->invalid;
            $this->fail('Should have thrown an exception');
        } catch (\ReflectionException $e) {
            $this->assert($e->getMessage(), "Property [" . InjectMocksTest_InjectableClass::class . "::invalid] " .
                "cannot be mocked since it's type hint is not a class.");
        }
    }

    function testStubMethodsOfConstructorInjectedMocks() {
        /** @var Mockster|InjectMocksTest_InjectableClass $injectable */
        $injectable = new Mockster(InjectMocksTest_InjectableClass::class);
        /** @var InjectMocksTest_InjectableClass $mock */
        $mock = $injectable->uut();

        Mockster::stub($injectable->bas->foo())->will()->return_('foo');
        $this->assert($mock->bas->foo(), 'foo');
    }

    function testInjectFactory() {
        $factory = new Factory();
        $factory->setSingleton(new \DateTime(), InjectMocksTest_FooClass::class);

        /** @var InjectMocksTest_InjectableClass $mock */
        $mock = (new Mockster(InjectMocksTest_InjectableClass::class, $factory))->uut();

        $this->assert($mock->bar, new \DateTime());
    }
}

class InjectMocksTest_InjectableClass {

    /** @var InjectMocksTest_FooClass */
    public $bar;

    /** @var string|InjectMocksTest_FooClass|mixed */
    public $multi;

    /** @var null|InjectMocksTest_FooClass */
    public $nullable;

    /** @var null|string */
    public $invalid;

    /** @var InjectMocksTest_FooClass */
    public $bas;

    /**
     * @param InjectMocksTest_FooClass $foo <-
     * @param InjectMocksTest_FooClass $bas <-
     */
    function __construct($foo, $bas) {
        $this->foo = $foo;
        $this->bas = $bas;
    }
}

class InjectMocksTest_FooClass {
    public $constructorCalled = false;

    function __construct() {
        $this->constructorCalled = true;
    }
    
    function foo() {
        return null;
    }
}