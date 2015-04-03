<?php
namespace spec\rtens\mockster;

use rtens\mockster\Mockster;
use rtens\mockster\Stubs;
use watoki\scrut\Specification;

class StubMethodsTest extends Specification {

    /** @var Foo $mock */
    private $mock;

    /** @var Foo|Mockster $foo */
    private $foo;

    protected function setUp() {
        parent::setUp();
        $this->foo = new Mockster(Foo::class);
        $this->mock = $this->foo->mock();
    }

    function testReturnValue() {
        Mockster::method($this->foo->bar())->will()->return_('foobar');

        $this->assertEquals('foobar', $this->mock->bar());
        $this->assertEquals('foobar', $this->mock->bar());
    }

    function testReturnValueOnce() {
        Mockster::method($this->foo->bar())->will()->return_('foo')->once();
        Mockster::method($this->foo->bar())->will()->return_('bar');

        $this->assertEquals('foo', $this->mock->bar());
        $this->assertEquals('bar', $this->mock->bar());
    }

    function testThrowException() {
        Mockster::method($this->foo->bar())->will()->throw_(new \InvalidArgumentException());

        try {
            $this->mock->bar();
            $this->fail("Should have thrown an exception");
        } catch (\InvalidArgumentException $ignored) {}
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