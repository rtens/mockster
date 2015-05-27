<?php
namespace spec\rtens\mockster;

use rtens\mockster\arguments\Argument;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;

class RecordCallsSpec extends StaticTestSuite {

    /** @var Mockster|RecordStubUsageTest_FooClass $foo */
    public $foo;

    /** @var RecordStubUsageTest_FooClass $mock */
    public $mock;

    protected function before() {
        $this->foo = new Mockster(RecordStubUsageTest_FooClass::class);
        $this->mock = $this->foo->mock();
    }

    function testNoCallRecorded() {
        try {
            Mockster::stub($this->foo->foo())->has()->inCall(0);
            $this->fail("Should throw Exception");
        } catch (\InvalidArgumentException $e) {
            $this->assert->contains($e->getMessage(), 'No call [0] recorded');
        }
    }

    function testRecordInvocations() {
        $this->assert->not(Mockster::stub($this->foo->foo())->has()->beenCalled());

        $this->mock->foo();
        $this->assert(Mockster::stub($this->foo->foo())->has()->beenCalled());
        $this->assert(Mockster::stub($this->foo->foo())->has()->beenCalled(1));

        $this->mock->foo();
        $this->assert(Mockster::stub($this->foo->foo())->has()->beenCalled(2));
    }

    function testRecordArguments() {
        $this->mock->foo('one', 'two');

        $this->assert(Mockster::stub($this->foo->foo('one', 'two'))->has()->inCall(0)->argument(0), 'one');
        $this->assert(Mockster::stub($this->foo->foo('one', 'two'))->has()->inCall(0)->argument('a'), 'one');
        $this->assert(Mockster::stub($this->foo->foo('one', 'two'))->has()->inCall(0)->argument(1), 'two');
        $this->assert(Mockster::stub($this->foo->foo('one', 'two'))->has()->inCall(0)->argument('b'), 'two');
    }

    function testRecordDefaultParameters() {
        $this->mock->foo('one');

        $this->assert(Mockster::stub($this->foo->foo('one'))->has()->inCall(0)->argument(1), null);
        $this->assert(Mockster::stub($this->foo->foo('one'))->has()->inCall(0)->argument('b'), null);

        $this->assert(Mockster::stub($this->foo->foo('one'))->has()->beenCalled());
        $this->assert(Mockster::stub($this->foo->foo('one', null))->has()->beenCalled());

        $this->assert(Mockster::stub($this->foo->foo(Argument::any()))->has()->beenCalled());
        $this->assert(Mockster::stub($this->foo->foo(Argument::any(), Argument::any()))->has()->beenCalled());

        $this->assert->not(Mockster::stub($this->foo->foo(null, null))->has()->beenCalled());
    }

    function testRecordReturnValue() {
        Mockster::stub($this->foo->foo())->dontStub();

        $this->mock->foo();
        $this->assert(Mockster::stub($this->foo->foo())->has()->inCall(0)->returned(), 'foo');
    }

    function testRecordThrownException() {
        Mockster::stub($this->foo->danger())->dontStub();

        try {
            $this->mock->danger();
        } catch (\InvalidArgumentException $ignored) {
        }

        $this->assert->isInstanceOf(Mockster::stub($this->foo->danger())->has()->inCall(0)->thrown(), \InvalidArgumentException::class);
    }

    function testFindStubByGeneralArguments() {
        $this->mock->foo('one');
        $this->mock->foo('two');
        $this->mock->foo('three');

        $this->assert->not(Mockster::stub($this->foo->foo('foo'))->has()->beenCalled(1));
        $this->assert(Mockster::stub($this->foo->foo('one'))->has()->beenCalled(1));
        $this->assert(Mockster::stub($this->foo->foo('two'))->has()->beenCalled(1));
        $this->assert(Mockster::stub($this->foo->foo(Argument::any()))->has()->beenCalled(3));

        $this->assert(Mockster::stub($this->foo->foo(Argument::any()))->has()->inCall(0)->argument(0), 'one');
        $this->assert(Mockster::stub($this->foo->foo(Argument::any()))->has()->inCall(2)->argument(0), 'three');
    }

    function testFindStubByMoreSpecificArgument() {
        Mockster::stub($this->foo->foo(Argument::any()))->will()->return_("foo");

        $this->mock->foo("one");

        $this->assert(Mockster::stub($this->foo->foo("one"))->has()->beenCalled());
        $this->assert->not(Mockster::stub($this->foo->foo("two"))->has()->beenCalled());
    }

    function testFindStubByInBetweenSpecificArgument() {
        Mockster::stub($this->foo->foo('one'))->will()->return_('uno');
        Mockster::stub($this->foo->foo('two'))->will()->return_('uno');
        Mockster::stub($this->foo->foo('three'))->will()->return_('uno');
        Mockster::stub($this->foo->foo(Argument::any()))->will()->return_('dos');

        $this->mock->foo('one');
        $this->mock->foo(1.0);
        $this->mock->foo('two');
        $this->mock->foo(1);
        $this->mock->foo('three');

        $this->assert->size(Mockster::stub($this->foo->foo(Argument::string()))->has()->calls(), 3);
        $this->assert->size(Mockster::stub($this->foo->foo(Argument::integer()))->has()->calls(), 1);
    }

    function testGetHistory() {
        Mockster::stub($this->foo->foo(Argument::any(), Argument::any()))
            ->will()->return_('foo')->once()
            ->then()->return_(['foo'])->once()
            ->then()->return_(new \DateTime())->once()
            ->then()->throw_(new \InvalidArgumentException("Oh no"));

        $this->mock->foo(4, 2);
        $this->mock->foo('One', 'Two');
        $this->mock->foo('Three');

        try {
            $this->mock->foo('Four');
        } catch (\InvalidArgumentException $ignored) {
        }

        $this->assert(Mockster::stub($this->foo->foo())->has()->printedHistory(),
            "No calls recorded for [" . RecordStubUsageTest_FooClass::class . "::foo()]");

        $this->assert(Mockster::stub($this->foo->foo(Argument::integer(), Argument::integer()))->has()->printedHistory(),
            "History of [" . RecordStubUsageTest_FooClass::class . "::foo()]\n" .
            "  foo(4, 2) -> 'foo'");

        $this->assert(Mockster::stub($this->foo->foo(Argument::string(), Argument::any()))->has()->printedHistory(),
            "History of [" . RecordStubUsageTest_FooClass::class . "::foo()]\n" .
            "  foo('One', 'Two') -> array\n" .
            "  foo('Three', NULL) -> DateTime\n" .
            "  foo('Four', NULL) !! InvalidArgumentException('Oh no')");

        echo Mockster::stub($this->foo->foo(Argument::any(), Argument::any()))->has()->printedHistory();
    }
}

class RecordStubUsageTest_FooClass {

    /**
     * @param null $a
     * @param null $b
     * @return null|mixed
     */
    public function foo($a = null, $b = null) {
        return 'foo' . $a . $b;
    }

    /**
     * @throws \InvalidArgumentException
     * @return null
     */
    public function danger() {
        throw new \InvalidArgumentException;
    }
}