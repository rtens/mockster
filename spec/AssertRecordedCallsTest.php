<?php namespace spec\rtens\mockster;

use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;

/**
 * @property \rtens\scrut\fixtures\ExceptionFixture try <-
 */
class AssertRecordedCallsTest extends StaticTestSuite {

    /** @var AssertRecordedCallsTest_FooClass $foo */
    public $foo;

    /** @var AssertRecordedCallsTest_FooClass $mock */
    public $mock;

    public function before() {
        $this->foo = new Mockster(AssertRecordedCallsTest_FooClass::class);
        $this->mock = $this->foo->__mock();
    }

    function testNotCalled() {
        $this->try->tryTo(function () {
            Mockster::stub($this->foo->foo())->shouldHave()->beenCalled();
        });
        $this->try->thenTheException_ShouldBeThrown(
            AssertRecordedCallsTest_FooClass::class . '::foo() was not called' . "\n" .
            "History of [" . AssertRecordedCallsTest_FooClass::class . ']' . "\n" .
            '  ');
    }

    function testNotCalledWithArguments() {
        $this->mock->bar('uno', 'dos');

        $this->try->tryTo(function () {
            Mockster::stub($this->foo->bar('one', 'two'))->shouldHave()->beenCalled();
        });
        $this->try->thenAnExceptionContaining_ShouldBeThrown("bar('one', 'two') was not called");
    }

    function testNotCalledOnce() {
        $this->try->tryTo(function () {
            Mockster::stub($this->foo->foo())->shouldHave()->beenCalled(1);
        });
        $this->try->thenAnExceptionContaining_ShouldBeThrown('foo() was not called 1 time');
    }

    function testNotCalledSeveralTimes() {
        $this->try->tryTo(function () {
            Mockster::stub($this->foo->foo())->shouldHave()->beenCalled(13);
        });
        $this->try->thenAnExceptionContaining_ShouldBeThrown('foo() was not called 13 times');
    }

    function testPrintHistory() {
        $this->mock->bar('uno', 'dos');
        $this->mock->bar('un', 'deux');

        $this->try->tryTo(function () {
            Mockster::stub($this->foo->bar('one', 'two'))->shouldHave()->beenCalled();
        });

        $this->try->thenAnExceptionContaining_ShouldBeThrown(
            "History of [" . AssertRecordedCallsTest_FooClass::class . ']' . "\n" .
            "  bar('uno', 'dos') -> NULL" . "\n" .
            "  bar('un', 'deux') -> NULL");
    }

    function testSuccessfulAssertion() {
        $this->mock->bar('one', 'two');

        $this->try->tryTo(function () {
            Mockster::stub($this->foo->bar('one', 'two'))->shouldHave()->beenCalled(1);
        });
        $this->try->thenNoExceptionShouldBeThrown();
    }

    function testAssertIndividualCall() {
        $this->try->tryTo(function () {
            Mockster::stub($this->foo->foo())->shouldHave()->inCall(0)->returned('bar');
        });

        $this->try->thenTheException_ShouldBeThrown(
            'No call [0] recorded.' . "\n" .
            "History of [" . AssertRecordedCallsTest_FooClass::class . ']' . "\n" .
            '  ');
    }

    function testAssertReturnValue() {
        Mockster::stub($this->foo->foo())->will()->return_('foo');
        $this->mock->foo();

        $this->try->tryTo(function () {
            Mockster::stub($this->foo->foo())->shouldHave()->inCall(0)->returned('bar');
        });

        $this->try->thenTheException_ShouldBeThrown(
            AssertRecordedCallsTest_FooClass::class . "::foo() did not return 'bar'" . "\n" .
            " The returned value was 'foo'");
    }

    function testSuccessfullyAssertReturnValue() {
        Mockster::stub($this->foo->foo())->will()->return_('foo');
        $this->mock->foo();

        $this->try->tryTo(function () {
            Mockster::stub($this->foo->foo())->shouldHave()->inCall(0)->returned('foo');
        });
        $this->try->thenNoExceptionShouldBeThrown();
    }

    function testAssertThrownException() {
        Mockster::stub($this->foo->foo())->will()->throw_(new \Exception('Oh oh'));
        try {
            $this->mock->foo();
        } catch (\Exception $ignored) {
        }

        $this->try->tryTo(function () {
            Mockster::stub($this->foo->foo())->shouldHave()->inCall(0)->thrown(new \InvalidArgumentException('Oh oh'));
        });

        $this->try->thenTheException_ShouldBeThrown(
            AssertRecordedCallsTest_FooClass::class . "::foo() did not throw <InvalidArgumentException>('Oh oh')" . "\n" .
            " The thrown exception was <Exception>('Oh oh')");
    }

    function testAssertNotThrownException() {
        $this->mock->foo();

        $this->try->tryTo(function () {
            Mockster::stub($this->foo->foo())->shouldHave()->inCall(0)->thrown(new \InvalidArgumentException('Oh oh'));
        });

        $this->try->thenTheException_ShouldBeThrown(
            AssertRecordedCallsTest_FooClass::class . "::foo() did not throw <InvalidArgumentException>('Oh oh')" . "\n" .
            " No exception was thrown.");
    }

    function testSuccessfullyAssertThrownException() {
        Mockster::stub($this->foo->foo())->will()->throw_(new \InvalidArgumentException('Oh oh'));
        try {
            $this->mock->foo();
        } catch (\Exception $ignored) {
        }

        $this->try->tryTo(function () {
            Mockster::stub($this->foo->foo())->shouldHave()->inCall(0)->thrown(new \InvalidArgumentException('Oh oh'));
        });

        $this->try->thenNoExceptionShouldBeThrown();
    }

}

class AssertRecordedCallsTest_FooClass {

    /**
     * @return null
     */
    public function foo() {
    }

    /**
     * @param $a
     * @param $b
     * @return null
     */
    public function bar($a, $b) {
    }
}