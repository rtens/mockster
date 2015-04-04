<?php
namespace spec\rtens\mockster3;

use rtens\mockster3\Mockster;
use watoki\scrut\Specification;

class RecordStubUsageTest extends Specification {

    function testRecordInvocations() {
        /** @var Mockster|RecordStubUsageTest_FooClass $foo */
        $foo = new Mockster(RecordStubUsageTest_FooClass::class);
        /** @var RecordStubUsageTest_FooClass $mock */
        $mock = $foo->mock();

        $this->assertEmpty(Mockster::stub($foo->foo())->calls);

        $mock->foo();

        $this->assertCount(1, Mockster::stub($foo->foo())->calls);
    }

    function testRecordArguments() {
        $this->markTestIncomplete();
    }

    function testRecordReturnValue() {
        $this->markTestIncomplete();
    }

    function testRecordThrownException() {
        $this->markTestIncomplete();
    }
}

class RecordStubUsageTest_FooClass {

    /**
     * @return null
     */
    public function foo() {
        return null;
    }
}