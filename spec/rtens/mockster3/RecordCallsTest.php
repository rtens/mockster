<?php
namespace spec\rtens\mockster3;

use rtens\mockster3\arguments\Argument;
use rtens\mockster3\Mockster;
use watoki\scrut\Specification;

class RecordCallsTest extends Specification {

    /** @var Mockster|RecordStubUsageTest_FooClass $foo */
    public $foo;

    /** @var RecordStubUsageTest_FooClass $mock */
    public $mock;

    protected function setUp() {
        parent::setUp();
        $this->foo = new Mockster(RecordStubUsageTest_FooClass::class);
        $this->mock = $this->foo->mock();
    }

    function testRecordInvocations() {
        $this->assertFalse(Mockster::stub($this->foo->foo())->has()->beenCalled());

        $this->mock->foo();
        $this->assertTrue(Mockster::stub($this->foo->foo())->has()->beenCalled(1));

        $this->mock->foo();
        $this->assertTrue(Mockster::stub($this->foo->foo())->has()->beenCalled(2));
    }

    function testRecordArguments() {
        $this->mock->foo('one', 'two');

        $this->assertEquals('one', Mockster::stub($this->foo->foo('one', 'two'))->has()->call(0)->argument(0));
        $this->assertEquals('one', Mockster::stub($this->foo->foo('one', 'two'))->has()->call(0)->argument('a'));
        $this->assertEquals('two', Mockster::stub($this->foo->foo('one', 'two'))->has()->call(0)->argument(1));
        $this->assertEquals('two', Mockster::stub($this->foo->foo('one', 'two'))->has()->call(0)->argument('b'));
    }

    function testRecordReturnValue() {
        Mockster::stub($this->foo->foo())->dontStub();

        $this->mock->foo();
        $this->assertEquals('bar', Mockster::stub($this->foo->foo())->has()->call(0)->returned());
    }

    function testRecordThrownException() {
        Mockster::stub($this->foo->danger())->dontStub();

        try {
            $this->mock->danger();
        } catch (\InvalidArgumentException $ignored) {
        }

        $this->assertInstanceOf(\InvalidArgumentException::class,
            Mockster::stub($this->foo->danger())->has()->call(0)->threw());
    }

    function testFindStubByGeneralArguments() {
        $this->mock->foo('one');
        $this->mock->foo('two');
        $this->mock->foo('three');

        $this->assertFalse(Mockster::stub($this->foo->foo('foo'))->has()->beenCalled());
        $this->assertTrue(Mockster::stub($this->foo->foo('one'))->has()->beenCalled());
        $this->assertTrue(Mockster::stub($this->foo->foo('two'))->has()->beenCalled());
        $this->assertTrue(Mockster::stub($this->foo->foo(Argument::any()))->has()->beenCalled());

        $this->assertEquals('one', Mockster::stub($this->foo->foo(Argument::any()))->has()->call(0)->argument(0));
        $this->assertEquals('three', Mockster::stub($this->foo->foo(Argument::any()))->has()->call(2)->argument(0));
    }

    function testFindStubByMoreSpecificArgument() {
        Mockster::stub($this->foo->foo(Argument::any()))->will()->return_("foo");

        $this->mock->foo("one");

        $this->assertTrue(Mockster::stub($this->foo->foo("one"))->has()->beenCalled());
        $this->assertFalse(Mockster::stub($this->foo->foo("two"))->has()->beenCalled());
    }
}

class RecordStubUsageTest_FooClass {

    /**
     * @param null $a
     * @param null $b
     * @return null|mixed
     */
    public function foo($a = null, $b = null) {
        return 'bar' . $a . $b;
    }

    /**
     * @throws \InvalidArgumentException
     * @return null
     */
    public function danger() {
        throw new \InvalidArgumentException;
    }
}