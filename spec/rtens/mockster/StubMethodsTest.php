<?php
namespace spec\rtens\mockster;

use rtens\mockster\arguments\Argument as Arg;
use rtens\mockster\exceptions\UndefinedBehaviourException;
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

    function testNoStubDefined() {
        try {
            $this->mock->bar();
            $this->fail("Should have thrown an exception");
        } catch (UndefinedBehaviourException $ignored) {}
    }

    function testReturnValue() {
        Mockster::stub($this->foo->bar())->will()->return_('foobar');

        $this->assertEquals('foobar', $this->mock->bar());
        $this->assertEquals('foobar', $this->mock->bar());
    }

    function testReturnValueOnce() {
        Mockster::stub($this->foo->bar())->will()->return_('foo')->once();
        Mockster::stub($this->foo->bar())->will()->return_('bar');

        $this->assertEquals('foo', $this->mock->bar());
        $this->assertEquals('bar', $this->mock->bar());
    }

    function testThrowException() {
        Mockster::stub($this->foo->bar())->will()->throw_(new \InvalidArgumentException());

        try {
            $this->mock->bar();
            $this->fail("Should have thrown an exception");
        } catch (\InvalidArgumentException $ignored) {
        }
    }

    function testCallCallback() {
        Mockster::stub($this->foo->bar('one', 'two'))->will()->call(function ($args) {
            return $args[0] . $args['b'];
        });

        $this->assertEquals('onetwo', $this->mock->bar('one', 'two'));
    }

    function testCallCallbackWithArguments() {
        Mockster::stub($this->foo->bar('uno', 'dos'))->will()->forwardTo(function ($a, $b) {
            return $a . $b;
        });

        $this->assertEquals('unodos', $this->mock->bar('uno', 'dos'));
    }

    function testDisableStubbing() {
        Mockster::stub($this->foo->bar())->dontStub();

        $this->assertEquals('original', $this->mock->bar());
    }

    function testMatchWithExactArguments() {
        Mockster::stub($this->foo->bar("uno", "dos"))->will()->return_("foo")->once();
        Mockster::stub($this->foo->bar("one", "two"))->will()->return_("bar");
        Mockster::stub($this->foo->bar("uno", "dos"))->will()->return_("baz")->once();

        $this->assertEquals("bar", $this->mock->bar("one", "two"));
        $this->assertEquals("foo", $this->mock->bar("uno", "dos"));
        $this->assertEquals("baz", $this->mock->bar("uno", "dos"));

        try {
            $this->mock->bar("not", "two");
            $this->fail("Should have thrown an execption");
        } catch (UndefinedBehaviourException $ignored) {
        }
    }

    function testMatchWithAnyArgument() {
        Mockster::stub($this->foo->bar(Arg::any(), Arg::any()))->will()->return_('foo');

        $this->assertEquals('foo', $this->mock->bar('one', 'two'));
        $this->assertEquals('foo', $this->mock->bar(null, true));
    }
}

class Foo {

    public function bar($a = null, $b = null) {
        return "original" . $a . $b;
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

    public function bar($a = null, $b = null) {
        $stub = $this->stubs->find('bar', func_get_args());

        if (!$stub->isStubbed()) {
            return parent::bar($a, $b);
        } else {
            return $stub->invoke([
                0 => $a,
                1 => $b,
                'a' => $a,
                'b' => $b
            ]);
        }
    }
}