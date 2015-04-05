<?php
namespace spec\rtens\mockster3;

use rtens\mockster3\Mockster;
use watoki\scrut\Specification;

class CheckReturnTypeTest extends Specification {

    /** @var CheckReturnTypeTest_FooClass $mock */
    private $mock;

    /** @var CheckReturnTypeTest_FooClass|Mockster $foo */
    private $foo;

    protected function setUp() {
        parent::setUp();

        $this->foo = new Mockster(CheckReturnTypeTest_FooClass::class);
        $this->mock = $this->foo->mock();
    }

    function testAcceptAllIfNoTypeHintGiven() {
        Mockster::stub($this->foo->noHint())->will()->return_("foo");
        Mockster::stub($this->foo->noHint())->will()->return_(42);
        $this->mock->noHint();
    }

    function testFailIfReturnedValuePrimitiveDoesNotMatch() {
        Mockster::stub($this->foo->returnsString())->will()->return_(42);

        try {
            $this->mock->returnsString();
            $this->fail("Should have thrown an exception");
        } catch (\ReflectionException $e) {
        }
        $this->assertCount(1, Mockster::stub($this->foo->returnsString())->calls());
    }
}

class CheckReturnTypeTest_FooClass {

    public function noHint() {
        return null;
    }

    /**
     * @return string
     */
    public function returnsString() {
        return null;
    }
}