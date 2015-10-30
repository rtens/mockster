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
            "  bar('uno', 'dos') -> NULL". "\n" .
            "  bar('un', 'deux') -> NULL");
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