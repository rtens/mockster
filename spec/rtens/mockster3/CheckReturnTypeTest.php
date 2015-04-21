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

    function testFailIfPrimitiveValueDoesNotMatch() {
        Mockster::stub($this->foo->returnsString())->will()->return_(42);

        try {
            $this->mock->returnsString();
            $this->fail("Should have thrown an exception");
        } catch (\ReflectionException $e) {
            $this->assertContains("does not match the return type", $e->getMessage());
        }
        $this->assertTrue(Mockster::stub($this->foo->returnsString())->has()->beenCalled());
    }

    function testFailIfNonStubbedValueDoesNotMatch() {
        Mockster::stub($this->foo->returnsString())->dontStub();

        try {
            $this->mock->returnsString();
            $this->fail("Should have thrown an exception");
        } catch (\ReflectionException $e) {
        }
        $this->assertTrue(Mockster::stub($this->foo->returnsString())->has()->beenCalled());
    }

    function testFailIfObjectDoesNotMatch() {
        Mockster::stub($this->foo->returnsDateTime())->will()->return_(new \DateTimeImmutable());

        try {
            $this->mock->returnsDateTime();
            $this->fail("Should have thrown an exception");
        } catch (\ReflectionException $e) {
        }
    }

    function testDisableChecking() {
        Mockster::stub($this->foo->returnsString())->will()->return_(null);
        Mockster::stub($this->foo->returnsString())->enableReturnTypeChecking(false);
        $this->mock->returnsString();
    }

    function testDisableCheckingGlobally() {
        Mockster::$enableReturnTypeChecking = false;
        Mockster::stub($this->foo->returnsString())->will()->return_(null);
        $this->mock->returnsString();
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

    /**
     * @return \DateTime
     */
    public function returnsDateTime() {
        return null;
    }
}