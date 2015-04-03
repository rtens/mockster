<?php
namespace spec\rtens\mockster\deprecated;

use spec\rtens\mockster\deprecated\fixtures\MockFactoryFixture;
use watoki\scrut\Specification;

/**
 * @property \spec\rtens\mockster\deprecated\fixtures\MockFactoryFixture fixture <-
 */
class CheckReturnValueTest extends Specification {

    public function testPrimitiveNotMatchingPrimitiveHint() {
        $this->fixture->givenTheClassDefinition('
            class PrimitiveNotMatchingPrimitiveHint {
                /**
                 * @return string
                 */
                public function shouldReturnAString() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('PrimitiveNotMatchingPrimitiveHint');
        $this->fixture->whenIConfigureTheMethod_ToReturn('shouldReturnAString', 1);
        $this->fixture->whenITryToInvoke('shouldReturnAString');

        $this->fixture->thenAnExceptionShouldBeThrownContaining('Expected return value of method PrimitiveNotMatchingPrimitiveHint:shouldReturnAString to be of one of the following types: [string]. Instead value is integer');
    }

    public function testObjectNotMatchingPrimitiveHint() {
        $this->fixture->givenTheClassDefinition('
            class ObjectNotMatchingHint {
                /**
                 * @return string
                 */
                public function shouldReturnAString() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('ObjectNotMatchingHint');
        $this->fixture->whenIConfigureTheMethod_ToReturn('shouldReturnAString', new \StdClass());
        $this->fixture->whenITryToInvoke('shouldReturnAString');

        $this->fixture->thenAnExceptionShouldBeThrownContaining('[string]. Instead value is stdClass');
    }

    public function testObjectNotMatchingClassHint() {
        $this->fixture->givenTheClassDefinition('
            class NotMatchingClassHint {}
        ');
        $this->fixture->givenTheClassDefinition('
            class ObjectNotMatchingClassHint {
                /**
                 * @return NotMatchingClassHint
                 */
                public function myFunction() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('ObjectNotMatchingClassHint');
        $this->fixture->whenIConfigureTheMethod_ToReturn('myFunction', new \StdClass());
        $this->fixture->whenITryToInvoke('myFunction');

        $this->fixture->thenAnExceptionShouldBeThrownContaining('[NotMatchingClassHint]. Instead value is stdClass');
    }

    public function testPrimitiveMatchingPrimitiveHint() {
        $this->fixture->givenTheClassDefinition('
            class PrimitiveMatchingPrimitiveHint {
                /**
                 * @return string
                 */
                public function shouldReturnAString() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('PrimitiveMatchingPrimitiveHint');
        $this->fixture->whenIConfigureTheMethod_ToReturn('shouldReturnAString', 'a string');
        $this->fixture->whenITryToInvoke('shouldReturnAString');

        $this->fixture->thenNoExceptionShouldBeThrown();
    }

    public function testObjectMatchingClassHint() {
        $this->fixture->givenTheClassDefinition('
            class ObjectMatchingClassHint {
                /**
                 * @return \StdClass
                 */
                public function shouldReturnObject() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('ObjectMatchingClassHint');
        $this->fixture->whenIConfigureTheMethod_ToReturn('shouldReturnObject', new \StdClass());
        $this->fixture->whenITryToInvoke('shouldReturnObject');

        $this->fixture->thenNoExceptionShouldBeThrown();
    }

    public function testNoTypeHint() {
        $this->fixture->givenTheClassDefinition('
            class NoTypeHint {
                public function myFunction() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('NoTypeHint');
        $this->fixture->whenIConfigureTheMethod_ToReturn('myFunction', new \StdClass());
        $this->fixture->whenITryToInvoke('myFunction');

        $this->fixture->thenNoExceptionShouldBeThrown();
    }

    public function testMultipleTypeHints() {
        $this->fixture->givenTheClassDefinition('
            class MultipleTypeHints {
                /**
                 * @return array|int|string|object
                 */
                public function myFunction() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('MultipleTypeHints');
        $this->fixture->whenIConfigureTheMethod_ToReturn('myFunction', new \StdClass());
        $this->fixture->whenITryToInvoke('myFunction');
        $this->fixture->thenNoExceptionShouldBeThrown();

        $this->fixture->whenIConfigureTheMethod_ToReturn('myFunction', 23);
        $this->fixture->whenITryToInvoke('myFunction');
        $this->fixture->thenNoExceptionShouldBeThrown();

        $this->fixture->whenIConfigureTheMethod_ToReturn('myFunction', array(1, 2, 3));
        $this->fixture->whenITryToInvoke('myFunction');
        $this->fixture->thenNoExceptionShouldBeThrown();

        $this->fixture->whenIConfigureTheMethod_ToReturn('myFunction', 'foo');
        $this->fixture->whenITryToInvoke('myFunction');
        $this->fixture->thenNoExceptionShouldBeThrown();

        $this->fixture->whenIConfigureTheMethod_ToReturn('myFunction', null);
        $this->fixture->whenITryToInvoke('myFunction');
        $this->fixture->thenAnExceptionShouldBeThrownContaining('[array,int,string,object]. Instead value is NULL');
    }

    public function testSubClassMatchingClassHint() {
        $this->fixture->givenTheClassDefinition('
            class SomeSubClassOfDateTime extends DateTime {}
        ');
        $this->fixture->givenTheClassDefinition('
            class SubClassMatchingClassHint {
                /**
                 * @return DateTime
                 */
                public function myFunction() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('SubClassMatchingClassHint');
        $this->fixture->whenIConfigureTheMethod_ToReturnAMockOf('myFunction', 'SomeSubClassOfDateTime');
        $this->fixture->whenITryToInvoke('myFunction');

        $this->fixture->thenNoExceptionShouldBeThrown();
    }

    public function testAliasedTypeHint() {
        $this->fixture->givenTheClassDefinition('
            namespace some\name\space;
            class SomeAliasedClass {}
        ');
        $this->fixture->givenTheClassDefinition('
            use some\name\space\SomeAliasedClass;

            class AliasedTypeHint {
                /**
                 * @return SomeAliasedClass
                 */
                public function myFunction() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('AliasedTypeHint');
        $this->fixture->whenIConfigureTheMethod_ToReturnAMockOf('myFunction', 'some\name\space\SomeAliasedClass');
        $this->fixture->whenITryToInvoke('myFunction');

        $this->fixture->thenNoExceptionShouldBeThrown();
    }

    public function testShortArrayNotation() {
        $this->fixture->givenTheClassDefinition('
            class ShortArrayNotation {
                /**
                 * @return DateTime[]
                 */
                public function myFunction() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('ShortArrayNotation');
        $this->fixture->whenIConfigureTheMethod_ToReturn('myFunction', array());
        $this->fixture->whenITryToInvoke('myFunction');

        $this->fixture->thenNoExceptionShouldBeThrown();
    }

    public function testHintedAsArrayButReturnsTraversable() {
        $this->fixture->givenTheClassDefinition('
            class HintedAsArrayButReturnsTraversable {
                /**
                 * @return DateTime[]
                 */
                public function myFunction() {}
            }
        ');

        $this->fixture->whenICreateTheMockOf('HintedAsArrayButReturnsTraversable');

        $this->fixture->whenIConfigureTheMethod_ToReturnAMockOf('myFunction', 'Iterator');
        $this->fixture->whenITryToInvoke('myFunction');
        $this->fixture->thenNoExceptionShouldBeThrown();

        $this->fixture->whenIConfigureTheMethod_ToReturnAMockOf('myFunction', 'IteratorAggregate');
        $this->fixture->whenITryToInvoke('myFunction');
        $this->fixture->thenNoExceptionShouldBeThrown();

        $this->fixture->whenIConfigureTheMethod_ToReturnAMockOf('myFunction', 'ArrayAccess');
        $this->fixture->whenITryToInvoke('myFunction');
        $this->fixture->thenAnExceptionShouldBeThrownContaining('types: [Traversable]');
    }

    public function testDoNoPerformCheckIfExplicitDisabled() {
        $this->fixture->givenTheClassDefinition('
            class DoNoPerformCheckIfExplicitDisabled {
                /**
                 * @return DateTime
                 */
                 public function myFunction() {}
            }
        ');

        $this->fixture->whenICreateTheMockOf('DoNoPerformCheckIfExplicitDisabled');
        $this->fixture->whenIConfigureTheMethod_ToReturn('myFunction', "a string");
        $this->fixture->whenIDisableTheReturnTypeHintCheckForTheMethod('myFunction');
        $this->fixture->whenITryToInvoke('myFunction');

        $this->fixture->thenNoExceptionShouldBeThrown();
    }
}
