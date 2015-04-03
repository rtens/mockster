<?php
namespace spec\rtens\mockster;

use rtens\mockster\Mockster;
use watoki\scrut\Specification;

class StubMethodsTest extends Specification {

    public function testFixedReturnValue() {
        /** @var Foo|Mockster $foo */
        $foo = new Mockster(Foo::class);

        Mockster::method($foo->bar())->willReturn('foobar');

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

class FooMock extends Foo {
    /**
     * @var \rtens\mockster\Mockster
     */
    private $mockster;

    function __construct(Mockster $mockster) {
        $this->mockster = $mockster;
    }

    public function bar() {
        //return $this->mockster->bar()->
    }
}