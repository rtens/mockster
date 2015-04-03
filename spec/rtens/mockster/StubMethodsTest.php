<?php
namespace spec\rtens\mockster;

use rtens\mockster\MockFactory;
use rtens\mockster\Mockster2;
use watoki\scrut\Specification;

class StubMethodsTest extends Specification {

    public function testFixedReturnValue() {
        $this->markTestIncomplete();

        $mf = new MockFactory();
        /** @var Foo|Mockster2 $foo */
        $foo = new Mockster2(Foo::class);

        Mockster2::method($foo->bar())->willReturn('foobar');

        /** @var Foo $mock */
        $mock = $foo->mock();
        $this->assertEquals('foobar', $mock->bar());
    }
}

class Foo {

    public function bar() {
        return null;
    }
}