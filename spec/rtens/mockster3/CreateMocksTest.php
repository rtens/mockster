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
        $this->mock = $this->foo->uut(['one' => 'uno', 'dos']);
        $this->assertEquals(['uno', 'dos'], $this->mock->constructorArguments);
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