<?php
namespace spec\rtens\mockster;

use rtens\mockster\Mockster;
use rtens\mockster\Stubs;
use watoki\scrut\Specification;

class StubMethodsTest extends Specification {

    public function testReturnValue() {
        /** @var Foo|Mockster $foo */
        $foo = new Mockster(Foo::class);

        Mockster::method($foo->bar())->will()->return_('foobar');

        /** @var Foo $mock */
        $mock = $foo->mock();
        $this->assertEquals('foobar', $mock->bar());
        $this->assertEquals('foobar', $mock->bar());
    }

    public function testReturnValueOnce() {
        /** @var Foo|Mockster $foo */
        $foo = new Mockster(Foo::class);

        Mockster::method($foo->bar())->will()->return_('foo')->once();
        Mockster::method($foo->bar())->will()->return_('bar');

        /** @var Foo $mock */
        $mock = $foo->mock();
        $this->assertEquals('foo', $mock->bar());
        $this->assertEquals('bar', $mock->bar());
    }
}

class Foo {

    public function bar() {
        return null;
    }
}

class FooMock extends Foo {

    /**
     * @var \rtens\mockster\Stubs
     */
    private $stubs;

    function __construct(Stubs $stubs) {
        $this->stubs = $stubs;
    }

    public function bar() {
        return $this->stubs->invoke('bar');
    }
}