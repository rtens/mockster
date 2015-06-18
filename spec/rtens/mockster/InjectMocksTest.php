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

        $this->assert->isNull($mock->not);

        $this->assert->isInstanceOf($mock->getProtected(), InjectMocksTest_FooClass::class);

        $this->assert->isInstanceOf($mock->bar, InjectMocksTest_FooClass::class);
        $this->assert->not($mock->bar->constructorCalled);
        $mock->bar->foo();
    }

    function testNotExistingProperty() {
        $injectable = new Mockster(InjectMocksTest_InjectableClass::class);

        try {
            /** @noinspection PhpUndefinedFieldInspection */
            $injectable->notExisting;
            $this->fail("Should have thrown an Exception");
        } catch (\ReflectionException $e) {
            $this->assert($e->getMessage(), "The property " .
                "[" . InjectMocksTest_InjectableClass::class . "::notExisting] does not exist");
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

        Mockster::stub($injectable->bar->foo())->will()->return_('foo');

        /** @var InjectMocksTest_InjectableClass $mock */
        $mock = $injectable->uut();

        Mockster::stub($injectable->bas->foo())->will()->return_('fos');

        $this->assert($mock->bar->foo(), 'foo');
        $this->assert($mock->bas->foo(), 'fos');
    }

    function testStubMethodsOfConstructorInjectedMocks() {
        /** @var Mockster|InjectMocksTest_InjectableClass $injectable */
        $injectable = new Mockster(InjectMocksTest_InjectableClass::class);
        /** @var InjectMocksTest_InjectableClass $mock */
        $mock = $injectable->uut();

        Mockster::stub($injectable->bas->foo())->will()->return_('foo');
        $this->assert($mock->bas->foo(), 'foo');
    }

    function testPassThroughConstructorArguments() {
        /** @var InjectMocksTest_InjectableClass $mock */
        $mock = (new Mockster(InjectMocksTest_InjectableClass::class))->uut([
            'bas' => new \DateTime()
        ]);

        $this->assert->isInstanceOf($mock->bas, \DateTime::class);
        $this->assert($mock->bas->format('c'), date('c'));
    }

    function testPassMockThroughConstructorArguments() {
        /** @var Mockster|InjectMocksTest_FooClass $injected */
        $injected = new Mockster(InjectMocksTest_FooClass::class);

        Mockster::stub($injected->foo())->will()->return_('bar');

        /** @var InjectMocksTest_InjectableClass $mock */
        $mock = (new Mockster(InjectMocksTest_InjectableClass::class))->uut([
            'bas' => $injected->mock()
        ]);

        $this->assert($mock->bas->foo(), 'bar');
    }

    function testPropertyStubbingOverwritesArgumentsStubbing() {
        /** @var Mockster|InjectMocksTest_InjectableClass $injectable */
        $injectable = new Mockster(InjectMocksTest_InjectableClass::class);
        /** @var Mockster|InjectMocksTest_FooClass $injected */
        $injected = new Mockster(InjectMocksTest_FooClass::class);

        Mockster::stub($injected->foo())->will()->return_('argument');
        Mockster::stub($injectable->bas->foo())->will()->return_('property');

        /** @var InjectMocksTest_InjectableClass $mock */
        $mock = $injectable->uut([
            'bas' => $injected->mock()
        ]);

        $this->assert($mock->bas->foo(), 'property');
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

    /** @var InjectMocksTest_FooClass <- */
    public $bar;

    /** @var InjectMocksTest_FooClass <- */
    protected $protected;

    /** @var InjectMocksTest_FooClass */
    public $not;

    /** @var InjectMocksTest_FooClass|\DateTime */
    public $bas;

    /**
     * @param InjectMocksTest_FooClass $foo <-
     * @param InjectMocksTest_FooClass $bas <-
     */
    function __construct($foo, $bas) {
        $this->foo = $foo;
        $this->bas = $bas;
    }

    /**
     * @return InjectMocksTest_FooClass
     */
    public function getProtected() {
        return $this->protected;
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