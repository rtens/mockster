<?php
namespace spec\rtens\mockster;

use rtens\mockster\arguments\Argument;
use rtens\mockster\arguments\Argument as Arg;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;

class StubMethodsTest extends StaticTestSuite {

    /** @var StubMethodsTest_FooClass $mock */
    private $mock;

    /** @var StubMethodsTest_FooClass|Mockster $foo */
    private $foo;

    public function before() {
        $this->foo = new Mockster(StubMethodsTest_FooClass::class);
        $this->mock = $this->foo->mock();
    }

    function testNoStubDefined() {
        $this->assert($this->mock->bar(), null);
        $this->assert($this->mock->foo(), null);
    }

    function testReturnValue() {
        Mockster::stub($this->foo->bar())->will()->return_('foobar');

        $this->assert($this->mock->bar(), 'foobar');
        $this->assert($this->mock->bar(), 'foobar');
    }

    function testReturnValueOnce() {
        Mockster::stub($this->foo->bar())->will()->return_('foo')->once()
            ->then()->return_('bar');

        $this->assert($this->mock->bar(), 'foo');
        $this->assert($this->mock->bar(), 'bar');
    }

    function testThrowException() {
        Mockster::stub($this->foo->bar())->will()->throw_(new \InvalidArgumentException());

        try {
            $this->mock->bar();
            $this->fail("Should have thrown an exception");
        } catch (\InvalidArgumentException $ignored) {
            $this->pass();
        }
    }

    function testCallCallback() {
        Mockster::stub($this->foo->bar('one', 'two'))->will()->call(function ($args) {
            return $args[0] . $args['b'];
        });

        $this->assert($this->mock->bar('one', 'two'), 'onetwo');
    }

    function testCallCallbackWithArguments() {
        Mockster::stub($this->foo->bar('uno', 'dos'))->will()->forwardTo(function ($a, $b) {
            return $a . $b;
        });

        $this->assert($this->mock->bar('uno', 'dos'), 'unodos');
    }

    function testCallCallbackWithDefaultArguments() {
        Mockster::stub($this->foo->bar(Argument::any(), Argument::any()))->will()->forwardTo(function ($a, $b) {
            return $a . $b;
        });

        $this->assert($this->mock->bar('uno'), 'uno');
    }

    function testDisableStubbing() {
        Mockster::stub($this->foo->bar())->dontStub();
        $this->assert($this->mock->bar(), 'original');
    }

    function testEnableStubbingWhenSettingBehaviour() {
        Mockster::stub($this->foo->bar())->dontStub();
        Mockster::stub($this->foo->bar())->will()->return_('bar');
        $this->assert($this->mock->bar(), "bar");
    }

    function testMatchWithExactArguments() {
        Mockster::stub($this->foo->bar("uno", "dos"))->will()->return_("foo");
        Mockster::stub($this->foo->bar("one", "two"))->will()->return_("bar");

        $this->assert($this->mock->bar("one", "two"), "bar");
        $this->assert($this->mock->bar("uno", "dos"), "foo");

        $this->assert($this->mock->bar("not", "two"), null);
    }

    function testMatchWithAnyArgument() {
        Mockster::stub($this->foo->bar(Arg::any(), Arg::any()))->will()->return_('foo');

        $this->assert($this->mock->bar('one', 'two'), 'foo');
        $this->assert($this->mock->bar(null, true), 'foo');
    }

    function testMatchWithDefaultArguments() {
        Mockster::stub($this->foo->bar())->will()->return_('foo');

        $this->assert($this->mock->bar(), 'foo');
        $this->assert($this->mock->bar(null), 'foo');
        $this->assert($this->mock->bar(null, null), 'foo');
    }

    function testCanNotGetMoreSpecificWithArguments() {
        Mockster::stub($this->foo->bar(Argument::any()))->will()->return_('foo');
        Mockster::stub($this->foo->bar('one'))->will()->return_('bar');

        $this->assert($this->mock->bar('one'), 'foo');
    }

    function testCanGetMoreGeneral() {
        Mockster::stub($this->foo->bar('one'))->will()->return_('bar');
        Mockster::stub($this->foo->bar(Argument::any()))->will()->return_('foo');

        $this->assert($this->mock->bar('one'), 'bar');
        $this->assert($this->mock->bar('two'), 'foo');
    }

    function testInheritedMethod() {
        /** @var Mockster|StubMethodsTest_FooChild $child */
        $child = new Mockster(StubMethodsTest_FooChild::class);

        Mockster::stub($child->bar())->will()->return_("foo");

        /** @var StubMethodsTest_FooChild $mock */
        $mock = $child->mock();
        $this->assert($mock->bar(), "foo");
    }

    function testNonExistingMethod() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            Mockster::stub($this->foo->nonExisting());
            $this->fail("Should have thrown an exception");
        } catch (\ReflectionException $e) {
            $this->assert->contains($e->getMessage(), "does not exist");
        }
    }

    function testProtectedMethods() {
        $foo = new Mockster(StubMethodsTest_FooClass::class);
        /** @noinspection PhpUndefinedMethodInspection */
        Mockster::stub($foo->protectedMethod())->will()->return_('bar');
        /** @noinspection PhpUndefinedMethodInspection */
        Mockster::stub($foo->proxyMethod())->dontStub();

        /** @var StubMethodsTest_FooClass $mock */
        $mock = $foo->mock();
        $this->assert($mock->proxyMethod(), "bar");
    }

    function testDoNotStubPrivateMethods() {
        try {
            $foo = new Mockster(StubMethodsTest_FooClass::class);
            /** @noinspection PhpUndefinedMethodInspection */
            Mockster::stub($foo->privateMethod());
            $this->fail("Should have thrown an exception");
        } catch (\ReflectionException $e) {
            $this->assert->contains($e->getMessage(), 'private methods');
            $this->assert->contains($e->getMessage(), 'FooClass::privateMethod');
        }
    }

    function testDoNotStubStaticMethods() {
        try {
            $foo = new Mockster(StubMethodsTest_FooClass::class);
            /** @noinspection PhpUndefinedMethodInspection */
            Mockster::stub($foo->staticMethod());
            $this->fail("Should have thrown an exception");
        } catch (\ReflectionException $e) {
            $this->assert->contains($e->getMessage(), 'static methods');
            $this->assert->contains($e->getMessage(), 'FooClass::staticMethod');
        }
    }
}

class StubMethodsTest_FooClass {

    public function foo() {
        return null;
    }

    /**
     * @param null|string $a
     * @param null|string $b
     * @return mixed
     * @throws \Exception
     */
    public function bar($a = null, $b = null) {
        return "original" . $a . $b;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function privateMethod() {
        return null;
    }

    protected function protectedMethod() {
        return null;
    }

    public function proxyMethod() {
        return $this->protectedMethod();
    }

    public static function staticMethod() {
        return null;
    }
}

class StubMethodsTest_FooChild extends StubMethodsTest_FooClass {
}