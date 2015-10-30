<?php
namespace spec\rtens\mockster;

use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;

class CheckReturnTypeTest extends StaticTestSuite {

    /** @var CheckReturnTypeTest_FooClass */
    private $mock;

    /** @var CheckReturnTypeTest_FooClass */
    private $uut;

    /** @var CheckReturnTypeTest_FooClass|Mockster */
    private $foo;

    public function before() {
        $this->foo = new Mockster(CheckReturnTypeTest_FooClass::class);
        $this->mock = $this->foo->__mock();
        $this->uut = $this->foo->__uut();
    }

    function testAcceptAllIfNoTypeHintGiven() {
        Mockster::stub($this->foo->noHint())->will()->return_("foo");
        Mockster::stub($this->foo->noHint())->will()->return_(42);
        $this->mock->noHint();
        $this->pass();
    }

    function testFailIfPrimitiveValueDoesNotMatch() {
        Mockster::stub($this->foo->returnsString())->will()->return_(new \DateTime('2011-12-13 14:15:16 UTC'));

        try {
            $this->mock->returnsString();
            $this->fail("Should have thrown an exception");
        } catch (\ReflectionException $e) {
            $this->assert($e->getMessage(), '[' . CheckReturnTypeTest_FooClass::class . '::returnsString()] ' .
                'returned [<DateTime>(2011-12-13T14:15:16+00:00)] which does not match its return type [string]');
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

    function testDisableCheckingForEntireMock() {
        Mockster::stub($this->foo->returnsDateTime())->will()->return_(null);

        $this->foo->enableReturnTypeChecking(false);

        Mockster::stub($this->foo->returnsString())->will()->return_(null);

        $this->mock->returnsString();
        $this->mock->returnsDateTime();
        $this->pass();
    }

    function testAnnotatedException() {
        try {
            $this->uut->throwsSomething();
        } catch (\InvalidArgumentException $e) {
            $this->assert($e->getMessage(), "Something");
        }
    }

    function testNotAnnotatedException() {
        try {
            $this->uut->throwsSomethingIllegally();
        } catch (\ReflectionException $e) {
            $this->assert($e->getMessage(), '[' . CheckReturnTypeTest_FooClass::class . '::throwsSomethingIllegally()] ' .
                'threw Exception(Something) without proper annotation');
        }
    }

    function testWronglyAnnotatedException() {
        try {
            $this->uut->throwsTheWrongThing();
        } catch (\ReflectionException $e) {
            $this->assert($e->getMessage(), '[' . CheckReturnTypeTest_FooClass::class . '::throwsTheWrongThing()] ' .
                'threw Exception() without proper annotation');
        }
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

    /**
     * @throws \BadMethodCallException|\InvalidArgumentException
     * @return \DateTime
     */
    public function throwsSomething() {
        throw new \InvalidArgumentException("Something");
    }

    /**
     * @return \DateTime
     */
    public function throwsSomethingIllegally() {
        throw new \Exception("Something");
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function throwsTheWrongThing() {
        throw new \Exception;
    }
}