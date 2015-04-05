<?php
namespace spec\rtens\mockster3;

use rtens\mockster3\arguments\Argument;
use rtens\mockster3\Mockster;
use watoki\scrut\Specification;

class RecordStubUsageTest extends Specification {

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
        $this->assertEmpty(Mockster::stub($this->foo->foo())->calls());
        $this->mock->foo();
        $this->assertCount(1, Mockster::stub($this->foo->foo())->calls());
        $this->mock->foo();
        $this->assertCount(2, Mockster::stub($this->foo->foo())->calls());
    }

    function testRecordArguments() {
        $this->mock->foo('one');
        $this->assertEquals('one', Mockster::stub($this->foo->foo('one'))->call(0)->argument(0));
        $this->assertEquals('one', Mockster::stub($this->foo->foo('one'))->call(0)->argument('a'));
    }

    function testRecordReturnValue() {
        Mockster::stub($this->foo->foo())->dontStub();
        $this->mock->foo();
        $this->assertEquals('bar', Mockster::stub($this->foo->foo())->call(0)->returnedValue());
    }

    function testRecordThrownException() {
        Mockster::stub($this->foo->danger())->dontStub();

        try {
            $this->mock->danger();
        } catch (\InvalidArgumentException $ignored) {
        }

        $this->assertInstanceOf(\InvalidArgumentException::class,
            Mockster::stub($this->foo->danger())->call(0)->thrownException());
    }

    function testFindStubByGeneralArguments() {
        $this->mock->foo('one');
        $this->mock->foo('two');
        $this->mock->foo('three');

        $this->assertCount(1, Mockster::stub($this->foo->foo('one'))->calls());
        $this->assertCount(1, Mockster::stub($this->foo->foo('two'))->calls());
        $this->assertCount(3, Mockster::stub($this->foo->foo(Argument::any()))->calls());

        $this->assertEquals('one', Mockster::stub($this->foo->foo(Argument::any()))->call(0)->argument(0));
        $this->assertEquals('three', Mockster::stub($this->foo->foo(Argument::any()))->call(2)->argument(0));
    }
}

class RecordStubUsageTest_FooClass {

    /**
     * @param null $a
     * @param null $b
     * @return null
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