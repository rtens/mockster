<?php
namespace spec\rtens\mockster3;

use rtens\mockster3\Mockster;
use watoki\scrut\Specification;

class CreateMocksTest extends Specification {

    function testPlainMock() {
        /** @var CreateMocksTest_FooClass|Mockster $foo */
        $foo = new Mockster(CreateMocksTest_FooClass::class);

        /** @var CreateMocksTest_FooClass $mock */
        $mock = $foo->mock();
        $this->assertFalse($mock->constructorCalled);
        $this->assertTrue(Mockster::stub($foo->foo())->isStubbed());
    }
}

class CreateMocksTest_FooClass {
    public $constructorCalled = false;

    function __construct() {
        $this->constructorCalled = true;
    }

    function foo() {
        return 'bar';
    }
}