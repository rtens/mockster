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