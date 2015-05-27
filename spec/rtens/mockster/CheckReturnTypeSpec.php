<?php
namespace spec\rtens\mockster;

use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;

class CheckReturnTypeSpec extends StaticTestSuite {

    /** @var CheckReturnTypeTest_FooClass $mock */
    private $mock;

    /** @var CheckReturnTypeTest_FooClass|Mockster $foo */
    private $foo;

    protected function before() {
        parent::before();

        $this->foo = new Mockster(CheckReturnTypeTest_FooClass::class);
        $this->mock = $this->foo->mock();
    }

    function testAcceptAllIfNoTypeHintGiven() {
        Mockster::stub($this->foo->noHint())->will()->return_("foo");
        Mockster::stub($this->foo->noHint())->will()->return_(42);
        $this->mock->noHint();
        $this->pass();
    }

    function testFailIfPrimitiveValueDoesNotMatch() {
        Mockster::stub($this->foo->returnsString())->will()->return_(new \DateTime());

        try {
            $this->mock->returnsString();
            $this->fail("Should have thrown an exception");
        } catch (\ReflectionException $e) {
            $this->assert($e->getMessage(), "The returned value [DateTime] does not match the return type hint of [" .
                CheckReturnTypeTest_FooClass::class . "::returnsString()]");
        }
        $this->assert(Mockster::stub($this->foo->returnsString())->has()->beenCalled());
    }

    function testFailIfNonStubbedValueDoesNotMatch() {
        Mockster::stub($this->foo->returnsString())->dontStub();

        try {
            $this->mock->returnsString();
            $this->fail("Should have thrown an exception");
        } catch (\ReflectionException $e) {
        }
        $this->assert(Mockster::stub($this->foo->returnsString())->has()->beenCalled());
    }

    function testFailIfObjectDoesNotMatch() {
        Mockster::stub($this->foo->returnsDateTime())->will()->return_(new \DateTimeImmutable());

        try {
            $this->mock->returnsDateTime();
            $this->fail("Should have thrown an exception");
        } catch (\ReflectionException $e) {
            $this->pass();
        }
    }

    function testDisableChecking() {
        Mockster::stub($this->foo->returnsString())->will()->return_(null);
        Mockster::stub($this->foo->returnsString())->enableReturnTypeChecking(false);
        $this->mock->returnsString();
        $this->pass();
    }

    function testDisableCheckingGlobally() {
        Mockster::$enableReturnTypeChecking = false;
        Mockster::stub($this->foo->returnsString())->will()->return_(null);
        $this->mock->returnsString();
        $this->pass();
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