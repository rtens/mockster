<?php
namespace spec\rtens\mockster;

use rtens\mockster\arguments\Argument as Arg;
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

        $this->assert(Mockster::stub($this->foo->foo(Arg::any()))->has()->beenCalled());
        $this->assert(Mockster::stub($this->foo->foo(Arg::any(), Arg::any()))->has()->beenCalled());

        $this->assert->not(Mockster::stub($this->foo->foo(null, null))->has()->beenCalled());
    }

    function testReplayCall() {
        $this->mock->foo('one');

        Mockster::stub($this->foo->foo(Arg::any()))->has()->inCall(0)->recorded(function ($a, $b) {
            $this->assert($a, 'one');
            $this->assert($b, null);
        });
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
        $this->assert(Mockster::stub($this->foo->foo(Arg::any()))->has()->beenCalled(3));

        $this->assert(Mockster::stub($this->foo->foo(Arg::any()))->has()->inCall(0)->argument(0), 'one');
        $this->assert(Mockster::stub($this->foo->foo(Arg::any()))->has()->inCall(2)->argument(0), 'three');
    }

    function testFindStubByMoreSpecificArgument() {
        Mockster::stub($this->foo->foo(Arg::any()))->will()->return_("foo");

        $this->mock->foo("one");

        $this->assert(Mockster::stub($this->foo->foo("one"))->has()->beenCalled());
        $this->assert->not(Mockster::stub($this->foo->foo("two"))->has()->beenCalled());
    }

    function testFindStubByInBetweenSpecificArgument() {
        Mockster::stub($this->foo->foo('one'))->will()->return_('uno');
        Mockster::stub($this->foo->foo('two'))->will()->return_('uno');
        Mockster::stub($this->foo->foo('three'))->will()->return_('uno');
        Mockster::stub($this->foo->foo(Arg::any()))->will()->return_('dos');

        $this->mock->foo('one');
        $this->mock->foo(1.0);
        $this->mock->foo('two');
        $this->mock->foo(1);
        $this->mock->foo('three');

        $this->assert->size(Mockster::stub($this->foo->foo(Arg::string()))->has()->calls(), 3);
        $this->assert->size(Mockster::stub($this->foo->foo(Arg::integer()))->has()->calls(), 1);
    }

    function testGetHistory() {
        Mockster::stub($this->foo->foo(Arg::any(), Arg::any()))
            ->will()->return_('foo')->once()
            ->then()->return_(['foo'])->once()
            ->then()->return_(new \DateTime('2011-12-13 14:15:16 UTC'))->once()
            ->then()->throw_(new \InvalidArgumentException("Oh no"));

        $this->mock->foo(4, 2);
        $this->mock->foo('One', 'Two');
        $this->mock->foo('Three');

        try {
            $this->mock->foo('Four', new RecordStubUsageTest_ToString());
        } catch (\InvalidArgumentException $ignored) {
        }

        $this->assert(Mockster::stub($this->foo->foo())->has()->printedHistory(),
            "No calls recorded for [" . RecordStubUsageTest_FooClass::class . "::foo()]");

        $this->assert(Mockster::stub($this->foo->foo(Arg::integer(), Arg::integer()))->has()->printedHistory(),
            "History of [" . RecordStubUsageTest_FooClass::class . "::foo()]\n" .
            "  foo(4, 2) -> 'foo'");

        $this->assert(Mockster::stub($this->foo->foo(Arg::string(), Arg::any()))->has()->printedHistory(),
            "History of [" . RecordStubUsageTest_FooClass::class . "::foo()]\n" .
            "  foo('One', 'Two') -> ['foo']\n" .
            "  foo('Three', NULL) -> <DateTime>(2011-12-13T14:15:16+00:00)\n" .
            "  foo('Four', <" . RecordStubUsageTest_ToString::class . ">('foo')) !! InvalidArgumentException('Oh no')");
    }
}

class RecordStubUsageTest_FooClass {

    /**
     * @param null $a
     * @param null $b
     * @return null|mixed
     * @throws \Exception
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

class RecordStubUsageTest_ToString {
    function __toString() {
        return 'foo';
    }
}