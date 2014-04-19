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

    public function testMockReturnsScalarValueNotMatchingTypeHint() {
        $this->fixture->givenTheClassDefinition('
            class ReturnsScalarValueNotMatchingTypeHint {
                /**
                 * @return string
                 */
                public function shouldReturnAString() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('ReturnsScalarValueNotMatchingTypeHint');
        $this->fixture->whenIConfigureTheMethod_ToReturn('shouldReturnAString', 1);
        $this->fixture->whenITryToInvoke('shouldReturnAString');

        $this->fixture->thenAnExceptionShouldBeThrownContaining('Expected return value of method ReturnsScalarValueNotMatchingTypeHint:shouldReturnAString to be of one of the following types: [string]. Instead value is integer');
    }

    public function testMockReturnsObjectNotMatchingTypeHint() {
        $this->fixture->givenTheClassDefinition('
            class ReturnsObjectNotMatchingTypeHint {
                /**
                 * @return string
                 */
                public function shouldReturnAString() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('ReturnsObjectNotMatchingTypeHint');
        $this->fixture->whenIConfigureTheMethod_ToReturn('shouldReturnAString', new \StdClass());
        $this->fixture->whenITryToInvoke('shouldReturnAString');

        $this->fixture->thenAnExceptionShouldBeThrownContaining('Expected return value of method ReturnsObjectNotMatchingTypeHint:shouldReturnAString to be of one of the following types: [string]. Instead value is stdClass');
    }

    public function testMockReturnsObjectNotMatchingClassTypeHint() {
        $this->fixture->givenTheClassDefinition('
            class ReturnsObjectNotMatchingClassTypeHint_ClassToReturn {}
        ');
        $this->fixture->givenTheClassDefinition('
            class ReturnsObjectNotMatchingClassTypeHint {
                /**
                 * @return ReturnsObjectNotMatchingClassTypeHint_ClassToReturn
                 */
                public function myFunction() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('ReturnsObjectNotMatchingClassTypeHint');
        $this->fixture->whenIConfigureTheMethod_ToReturn('myFunction', new \StdClass());
        $this->fixture->whenITryToInvoke('myFunction');

        $this->fixture->thenAnExceptionShouldBeThrownContaining('Expected return value of method ReturnsObjectNotMatchingClassTypeHint:myFunction to be of one of the following types: [ReturnsObjectNotMatchingClassTypeHint_ClassToReturn]. Instead value is stdClass');
    }

    public function testMockReturnsScalarValueMatchingTypeHint() {
        $this->fixture->givenTheClassDefinition('
            class ReturnsScalarValueMatchingTypeHint {
                /**
                 * @return string
                 */
                public function shouldReturnAString() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('ReturnsScalarValueMatchingTypeHint');
        $this->fixture->whenIConfigureTheMethod_ToReturn('shouldReturnAString', 'a string');
        $this->fixture->whenIInvoke('shouldReturnAString');

        $this->fixture->thenItShouldReturn('a string');
    }

    public function testMockReturnsObjectMatchingTypeHint() {
        $this->fixture->givenTheClassDefinition('
            class ReturnsObjectMatchingTypeHint {
                /**
                 * @return \StdClass
                 */
                public function shouldReturnObject() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('ReturnsObjectMatchingTypeHint');
        $this->fixture->whenIConfigureTheMethod_ToReturn('shouldReturnObject', new \StdClass());
        $this->fixture->whenIInvoke('shouldReturnObject');

        $this->fixture->thenItShouldReturn(new \StdClass());
    }

    public function testMockHasNoTypeHint() {
        $this->fixture->givenTheClassDefinition('
            class HasNoTypeHint {
                public function myFunction() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('HasNoTypeHint');
        $this->fixture->whenIConfigureTheMethod_ToReturn('myFunction', new \StdClass());
        $this->fixture->whenIInvoke('myFunction');

        $this->fixture->thenItShouldReturn(new \StdClass());
    }

    public function testMockReturnsValueAndHasMultipleTypeHints() {
        $this->fixture->givenTheClassDefinition('
            class ReturnsValueAndHasMultipleTypeHints {
                /**
                 * @return array|int|string|object
                 */
                public function myFunction() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('ReturnsValueAndHasMultipleTypeHints');
        $this->fixture->whenIConfigureTheMethod_ToReturn('myFunction', new \StdClass());
        $this->fixture->whenIInvoke('myFunction');
        $this->fixture->thenItShouldReturn(new \StdClass());

        $this->fixture->whenIConfigureTheMethod_ToReturn('myFunction', 23);
        $this->fixture->whenIInvoke('myFunction');
        $this->fixture->thenItShouldReturn(23);

        $this->fixture->whenIConfigureTheMethod_ToReturn('myFunction', array(1,2,3));
        $this->fixture->whenIInvoke('myFunction');
        $this->fixture->thenItShouldReturn(array(1,2,3));

        $this->fixture->whenIConfigureTheMethod_ToReturn('myFunction', 'foo');
        $this->fixture->whenIInvoke('myFunction');
        $this->fixture->thenItShouldReturn('foo');

        $this->fixture->whenIConfigureTheMethod_ToReturn('myFunction', null);
        $this->fixture->whenITryToInvoke('myFunction');

        $this->fixture->thenAnExceptionShouldBeThrownContaining('Instead value is NULL');
    }

    public function testMockReturnsMock() {
        $this->fixture->givenTheClassDefinition('
            class MockReturnsMock_ClassToReturn {}
        ');
        $this->fixture->givenTheClassDefinition('
            class MockReturnsMock {
                /**
                 * @return MockReturnsMock_ClassToReturn
                 */
                public function myFunction() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('MockReturnsMock');
        $this->fixture->whenIConfigureTheMethod_ToReturnAMockOf('myFunction', 'MockReturnsMock_ClassToReturn');
        $this->fixture->whenIInvoke('myFunction');

        $this->fixture->thenItShouldReturnAnInstanceOf('MockReturnsMock_ClassToReturn');
    }

    public function testMockReturnsObjectReferencedInUseStatement() {
        $this->fixture->givenTheClassDefinition('
            namespace some\name\space;

            class ReturnsObjectReferencedInUseStatement_ClassToReturn {}
        ');
        $this->fixture->givenTheClassDefinition('
            use some\name\space\ReturnsObjectReferencedInUseStatement_ClassToReturn;

            class ReturnsObjectReferencedInUseStatement {
                /**
                 * @return ReturnsObjectReferencedInUseStatement_ClassToReturn
                 */
                public function myFunction() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('ReturnsObjectReferencedInUseStatement');
        $this->fixture->whenIConfigureTheMethod_ToReturnAMockOf('myFunction', 'some\name\space\ReturnsObjectReferencedInUseStatement_ClassToReturn');
        $this->fixture->whenIInvoke('myFunction');

        $this->fixture->thenItShouldReturnAnInstanceOf('some\name\space\ReturnsObjectReferencedInUseStatement_ClassToReturn');
    }
}
