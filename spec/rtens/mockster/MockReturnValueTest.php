<?php
namespace spec\rtens\mockster;
 
use watoki\scrut\Specification;

/**
 * @property MockFactoryFixture fixture <-
 */
class MockReturnValueTest extends Specification {

    public function testReturnMock() {
        $this->fixture->givenTheClassDefinition('
            class ReturnMock {
                /**
                 * @return StdClass
                 */
                public function myFunction() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('ReturnMock');
        $this->fixture->whenIInvoke('myFunction');

        $this->fixture->thenItShouldReturnAnInstanceOf('StdClass');
        $this->fixture->thenItShouldReturnAnInstanceOf('rtens\mockster\Mock');
    }

    public function testReturnPrimitive() {
        $this->fixture->givenTheClassDefinition('
            class ReturnPrimitives {
                /**
                 * @return int
                 */
                public function myInt() {}

                /**
                 * @return float
                 */
                public function myFloat() {}

                /**
                 * @return bool
                 */
                public function myBool() {}

                /**
                 * @return string
                 */
                public function myString() {}

                /**
                 * @return array
                 */
                public function myArray() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('ReturnPrimitives');

        $this->fixture->whenIInvoke('myInt');
        $this->fixture->thenItShouldReturn(0);

        $this->fixture->whenIInvoke('myFloat');
        $this->fixture->thenItShouldReturn(0.0);

        $this->fixture->whenIInvoke('myBool');
        $this->fixture->thenItShouldReturn(false);

        $this->fixture->whenIInvoke('myString');
        $this->fixture->thenItShouldReturn('');

        $this->fixture->whenIInvoke('myArray');
        $this->fixture->thenItShouldReturn(array());

    }

    public function testMultipleReturnTypeHints() {
        $this->fixture->givenTheClassDefinition('
            class ReturnMultiple {
                /**
                 * @return array|null|StdClass[]
                 */
                public function myFunction() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('ReturnMultiple');
        $this->fixture->whenIInvoke('myFunction');

        $this->fixture->thenItShouldReturn(array());
    }

    public function testInvalidHint() {
        $this->fixture->givenTheClassDefinition('
            class InvalidReturnHint {
                /**
                 * @return notAHint
                 */
                public function myFunction() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('InvalidReturnHint');
        $this->fixture->whenIInvoke('myFunction');

        $this->fixture->thenItShouldReturn(null);
    }

    public function testNoHint() {
        $this->fixture->givenTheClassDefinition('
            class NoReturnHint {
                public function myFunction() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('NoReturnHint');
        $this->fixture->whenIInvoke('myFunction');

        $this->fixture->thenItShouldReturn(null);
    }

    public function testMockCallChain() {
        $this->fixture->givenTheClassDefinition('
            class ChainedDependencyDependency {
                public function herFunction() {}
            }
        ');
        $this->fixture->givenTheClassDefinition('
            class ChainedDependency {
                /**
                 * @return ChainedDependencyDependency
                 */
                public function hisFunction() {}
            }
        ');
        $this->fixture->givenTheClassDefinition('
            class ChainedCall {
                /**
                 * @return ChainedDependency
                 */
                public function myFunction() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('ChainedCall');

        $this->fixture->whenIInvokeTheChain('myFunction()->hisFunction()->herFunction()');
        $this->fixture->thenItShouldReturn(null);

        $this->fixture->whenIConfigureTheChain_ToReturn('myFunction->hisFunction->herFunction', 'foo');
        $this->fixture->whenIInvokeTheChain('myFunction()->hisFunction()->herFunction()');
        $this->fixture->thenItShouldReturn('foo');
    }

}
