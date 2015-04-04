<?php
namespace spec\rtens\mockster3;

use rtens\mockster3\arguments\Argument as Arg;
use rtens\mockster3\exceptions\UndefinedBehaviourException;
use rtens\mockster3\Mockster;
use watoki\scrut\Specification;

class StubMethodsTest extends Specification {

    /** @var StubMethodsTest_FooClass $mock */
    private $mock;

    /** @var StubMethodsTest_FooClass|Mockster $foo */
    private $foo;

    protected function setUp() {
        parent::setUp();

        $this->foo = new Mockster(StubMethodsTest_FooClass::class);
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

class StubMethodsTest_FooClass {

    public function bar($a = null, $b = null) {
        return "original" . $a . $b;
    }
}