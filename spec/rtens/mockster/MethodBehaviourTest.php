<?php
namespace spec\rtens\mockster;

use watoki\scrut\Specification;

/**
 * @property MockFactoryFixture fixture <-
 */
class MethodBehaviourTest extends Specification {

    public function testMethodStubReturnsValue() {
        $this->fixture->givenTheClassDefinition('
            class ReturnValue {
                public function myFunction() {}
            }
        ');

        $this->fixture->whenICreateTheMockOf('ReturnValue');
        $this->fixture->whenIConfigureTheMethod_ToReturn('myFunction', 'foo');

        $this->fixture->whenIInvoke('myFunction');

        $this->fixture->thenItShouldReturn('foo');
    }

    public function testMethodStubCallback() {
        $this->fixture->givenTheClassDefinition('
            class Callback {
                public function myFunction($arg) {}
            }
        ');

        $this->fixture->whenICreateTheMockOf('Callback');
        $this->fixture->whenIConfigureTheMethod_ToCallAClosure('myFunction');

        $this->fixture->whenIInvoke_WithTheArgument('myFunction', 'hello');

        $this->fixture->thenTheCallbackShouldBeCalledWith('hello');
    }

    public function testThrowException() {
        $this->fixture->givenTheClassDefinition('
            class ThrowException {
                public function myFunction() {}
            }
        ');

        $this->fixture->whenICreateTheMockOf('ThrowException');
        $this->fixture->whenIConfigureTheMethod_ToThrowAnExceptionWithTheMessage('myFunction', 'Stay calm');

        $this->fixture->whenITryToInvoke('myFunction');
        $this->fixture->thenAnExceptionShouldBeThrownContaining('Stay calm');
    }

    public function testImplicitlyMockMethodWhenBehaviourDefined() {
        $this->fixture->givenTheClassDefinition('
            class ImplicitMocking {
                public $called = false;
                public function myFunction() {
                    $this->called = true;
                }
            }
        ');
        $this->fixture->whenICreateTheMockOf('ImplicitMocking');
        $this->fixture->whenIUnMockTheMethod('myFunction');

        $this->fixture->whenIConfigureTheMethod_ToReturn('myFunction', 'nothing');
        $this->fixture->whenIInvoke('myFunction');

        $this->fixture->thenItShouldReturn('nothing');
        $this->fixture->thenItsProperty_ShouldBe('called', false);
    }

    public function testReturnForCertainArguments() {
        $this->fixture->givenTheClassDefinition('
            class GivenArgument {
                public function myFunction($arg = null) {}
            }
        ');

        $this->fixture->whenICreateTheMockOf('GivenArgument');
        $this->fixture->whenIConfigureTheMethod_ToReturn_WhenCalledWithTheArgument('myFunction', 'something', 'a');

        $this->fixture->whenIInvoke('myFunction');
        $this->fixture->thenItShouldReturn(null);

        $this->fixture->whenIInvoke_WithTheArgument('myFunction', 'a');
        $this->fixture->thenItShouldReturn('something');

        $this->fixture->whenIInvoke_WithTheArgument('myFunction', 'b');
        $this->fixture->thenItShouldReturn(null);
    }

    public function testMultipleSingleReturns() {
        $this->fixture->givenTheClassDefinition('
            class ReturnOnce {
                public function myFunction() {}
            }
        ');

        $this->fixture->whenICreateTheMockOf('ReturnOnce');
        $this->fixture->whenIConfigureTheMethod_ToReturn_Once('myFunction', 'first');
        $this->fixture->whenIConfigureTheMethod_ToReturn_Once('myFunction', 'second');

        $this->fixture->whenIInvoke('myFunction');
        $this->fixture->thenItShouldReturn('second');

        $this->fixture->whenIInvoke('myFunction');
        $this->fixture->thenItShouldReturn('first');

        $this->fixture->whenIInvoke('myFunction');
        $this->fixture->thenItShouldReturn(null);
    }

    public function testMultipleSingleReturnsWithArguments() {
        $this->fixture->givenTheClassDefinition('
            class OnceWithArgument {
                public function myFunction($arg = null) {}
            }
        ');

        $this->fixture->whenICreateTheMockOf('GivenArgument');
        $this->fixture->whenIConfigureTheMethod_ToReturn_OnceWhenCalledWithTheArgument('myFunction', 'one', 'a');
        $this->fixture->whenIConfigureTheMethod_ToReturn_OnceWhenCalledWithTheArgument('myFunction', 'two', 'b');
        $this->fixture->whenIConfigureTheMethod_ToReturn_OnceWhenCalledWithTheArgument('myFunction', 'three', 'a');

        $this->fixture->whenIInvoke('myFunction');
        $this->fixture->thenItShouldReturn(null);

        $this->fixture->whenIInvoke_WithTheArgument('myFunction', 'a');
        $this->fixture->thenItShouldReturn('three');

        $this->fixture->whenIInvoke_WithTheArgument('myFunction', 'a');
        $this->fixture->thenItShouldReturn('one');

        $this->fixture->whenIInvoke_WithTheArgument('myFunction', 'b');
        $this->fixture->thenItShouldReturn('two');
    }

    public function testWithMixedArguments() {
        $this->fixture->givenTheClassDefinition('
            class WithMixedArguments {
                public function myFunction($one, $two, $three) {}
            }
        ');

        $this->fixture->whenICreateTheMockOf('WithMixedArguments');
        $this->fixture->whenIConfigureTheMethod_ToReturn_WhenCalledWith('myFunction', 'hit', array('two' => 2, 0 => 1));

        $this->fixture->whenIInvoke_WithTheArguments__And('myFunction', 1, 2, 3);
        $this->fixture->thenItShouldReturn('hit');

        $this->fixture->whenIInvoke_WithTheArguments__And('myFunction', 1, 2, 5);
        $this->fixture->thenItShouldReturn('hit');

        $this->fixture->whenIInvoke_WithTheArguments__And('myFunction', 2, 1, 3);
        $this->fixture->thenItShouldReturn(null);

        $this->fixture->whenIInvoke_WithTheArguments__And('myFunction', 2, 2, 3);
        $this->fixture->thenItShouldReturn(null);
    }

    public function testWithCallback() {
        $this->fixture->givenTheClassDefinition('
            class WithCallback {
                public function myFunction($arg) {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('WithCallback');
        $this->fixture->whenIConfigureTheMethod_ToReturn_WhenTheArgumentIsBetween_And('myFunction', 'me', 2, 5);

        $this->fixture->whenIInvoke_WithTheArgument('myFunction', 2);
        $this->fixture->thenItShouldReturn(null);

        $this->fixture->whenIInvoke_WithTheArgument('myFunction', 3);
        $this->fixture->thenItShouldReturn('me');

        $this->fixture->whenIInvoke_WithTheArgument('myFunction', 4);
        $this->fixture->thenItShouldReturn('me');

        $this->fixture->whenIInvoke_WithTheArgument('myFunction', 5);
        $this->fixture->thenItShouldReturn(null);
    }

}