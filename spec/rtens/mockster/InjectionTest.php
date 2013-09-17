<?php
namespace spec\rtens\mockster;

use watoki\scrut\Specification;

/**
 * @property MockFactoryFixture fixture <-
 */
class InjectionTest extends Specification {

    public function testInjectInConstructor() {
        $this->fixture->givenTheClassDefinition('class ConstructorDependencyOne {}');
        $this->fixture->givenTheClassDefinition('class ConstructorDependencyTwo {}');
        $this->fixture->givenTheClassDefinition('
            class ConstructorInjection {
                public function __construct(ConstructorDependencyOne $one, ConstructorDependencyTwo $two) {
                    $this->one = $one;
                    $this->two = $two;
                }
            }
        ');
        $this->fixture->whenICreateTheMockOf_WithTheConstructorArguments('ConstructorInjection', array());

        $this->fixture->thenItsProperty_ShouldBeAnInstanceOf('one', 'ConstructorDependencyOne');
        $this->fixture->thenItsProperty_ShouldBeAnInstanceOf('one', 'rtens\mockster\Mock');

        $this->fixture->thenItsProperty_ShouldBeAnInstanceOf('two', 'ConstructorDependencyTwo');
        $this->fixture->thenItsProperty_ShouldBeAnInstanceOf('two', 'rtens\mockster\Mock');
    }

    public function testNoInjectionIfConstructorIsNotCalled() {
        $this->fixture->givenTheClassDefinition('class NotInjected {}');
        $this->fixture->givenTheClassDefinition('
            class SkippedConstructor {
                public function __construct(NotInjected $foo) {
                    $this->foo = $foo;
                }
            }
        ');
        $this->fixture->whenICreateTheMockOf('SkippedConstructor');

        $this->fixture->thenItsProperty_ShouldBe('foo', null);
    }

    public function testNoRecursiveInjection() {
        $this->fixture->givenTheClassDefinition('class RecursiveDependencyOne {}');
        $this->fixture->givenTheClassDefinition('
            class RecursiveDependencyTwo {
                public $one;
                public function __construct(ConstructorDependencyOne $one) {
                    $this->one = $one;
                }
            }
        ');
        $this->fixture->givenTheClassDefinition('
            class RecursiveInjection {
                public function __construct(ConstructorDependencyTwo $two) {
                    $this->two = $two;
                }
            }
        ');
        $this->fixture->whenICreateTheMockOf_WithTheConstructorArguments('RecursiveInjection', array());

        $this->fixture->thenItsProperty_ShouldBeAnInstanceOf('two', 'ConstructorDependencyTwo');
        $this->fixture->thenItsProperty_OfProperty_ShouldBe('one', 'two', null);
    }

    public function testDontInjectDefaultArguments() {
        $this->fixture->givenTheClassDefinition('class NotInjectedAgain {}');
        $this->fixture->givenTheClassDefinition('
            class DefaultConstructorArguments {
                public $foo;
                public function __construct(NotInjectedAgain $foo = null) {
                    $this->foo = $foo;
                }
            }
        ');
        $this->fixture->whenICreateTheMockOf('DefaultConstructorArguments');

        $this->fixture->thenItsProperty_ShouldBe('foo', null);
    }

    public function testMixConstructorInjectionAndArguments() {
        $this->fixture->givenTheClassDefinition('
            class MixConstructor {
                public function __construct(StdClass $one, StdClass $two) {
                    $this->one = $one;
                    $this->two = $two;
                }
            }
        ');
        $this->fixture->whenICreateTheMockOf_WithTheConstructorArguments('MixConstructor', array('two' => new \StdClass));

        $this->fixture->thenItsProperty_ShouldBeAnInstanceOf('one', 'rtens\mockster\Mock');
        $this->fixture->thenItsProperty_ShouldNotBeAnInstanceOf('two', 'rtens\mockster\Mock');
    }

    public function testAccessArgumentsInjectedInConstructor() {
        $this->fixture->givenTheClassDefinition('
            class AccessArguments {
                public function __construct(StdClass $one, DateTime $two) {}
            }
        ');
        $this->fixture->whenICreateTheMockOf_WithTheConstructorArguments('AccessArguments', array());

        $this->fixture->thenTheInjectedArgument_ShouldBeAnInstanceOf('one', 'StdClass');
        $this->fixture->thenTheInjectedArgument_ShouldBeAnInstanceOf('two', 'rtens\mockster\Mock');
    }

    public function testInjectAnnotatedConstructorArguments() {
        $this->markTestSkipped("Doesn't work yet");

        $this->fixture->givenTheClassDefinition('
            class AnnotatedArguments {
                /**
                 * @param StdClass $foo
                 * @param StdClass $bar
                 */
                public function __construct($foo, DateTime $bar) {}
            }
        ');
        $this->fixture->whenICreateTheMockOf_WithTheConstructorArguments('AnnotatedArguments', array());

        $this->fixture->thenTheInjectedArgument_ShouldBeAnInstanceOf('foo', 'StdClass');
        $this->fixture->thenTheInjectedArgument_ShouldBeAnInstanceOf('foo', 'DateTime');
    }

    public function testInjectProperties() {
        $this->fixture->givenTheClassDefinition('
            class InjectProperties {
                /**
                 * @var StdClass
                 */
                public $foo;

                /**
                 * @var DateTime
                 */
                protected $bar;
            }
        ');
        $this->fixture->whenICreateTheMockOf('InjectProperties');
        $this->fixture->whenIMockAllOfItsProperties();

        $this->fixture->thenItsProperty_ShouldBeAnInstanceOf('foo', 'StdClass');
        $this->fixture->thenItsProperty_ShouldBeAnInstanceOf('bar', 'DateTime');
    }

    public function testInjectionOfPrimitiveTypes() {
        $this->fixture->givenTheClassDefinition('
            class PrimitiveProperties {
                /**
                 * @var array
                 */
                public $array;

                /**
                 * @var int
                 */
                public $int;
            }
        ');
        $this->fixture->whenICreateTheMockOf('PrimitiveProperties');
        $this->fixture->whenIMockAllOfItsProperties();

        $this->fixture->thenItsProperty_ShouldBe('array', array());
        $this->fixture->thenItsProperty_ShouldBe('int', 0);

        $this->markTestIncomplete('Should not inject primitives');
    }

    public function testInjectPropertiesWithSingleLineComments() {
        $this->markTestSkipped('Not working yet');

        $this->fixture->givenTheClassDefinition('
            class SingleLineComment {
                /** @var StdClass */
                public $foo;
            }
        ');
        $this->fixture->whenICreateTheMockOf('SingleLineComment');
        $this->fixture->whenIMockAllOfItsProperties();

        $this->fixture->thenItsProperty_ShouldBeAnInstanceOf('foo', 'StdClass');

    }

    public function testOnlyInjectAnnotatedProperties() {
        $this->fixture->givenTheClassDefinition('
            class InjectAnnotatedProperties {
                /**
                 * @var StdClass
                 */
                public $foo;

                /**
                 * @annotated
                 * @var StdClass
                 */
                public $bar;
            }
        ');
        $this->fixture->whenICreateTheMockOf('InjectAnnotatedProperties');
        $this->fixture->whenIMockAllOfItsPropertiesAnnotatedWith('annotated');

        $this->fixture->thenItsProperty_ShouldBe('foo', null);
        $this->fixture->thenItsProperty_ShouldBeAnInstanceOf('bar', 'StdClass');
    }

    public function testInjectOnlyProtectedProperties() {
        $this->fixture->givenTheClassDefinition('
            class InjectProtectedProperties {
                /**
                 * @var StdClass
                 */
                public $foo;

                /**
                 * @var DateTime
                 */
                protected $bar;
            }
        ');
        $this->fixture->whenICreateTheMockOf('InjectProtectedProperties');
        $this->fixture->whenIMockItsProtectedProperties();

        $this->fixture->thenItsProperty_ShouldBe('foo', null);
        $this->fixture->thenItsProperty_ShouldBeAnInstanceOf('bar', 'DateTime');
    }

    public function testInjectPropertiesWithMultipleTypehints() {
        $this->fixture->givenTheClassDefinition('
            class MultiTypeHint {
                /**
                 * @var StdClass|null
                 */
                public $foo;
            }
        ');
        $this->fixture->whenICreateTheMockOf('MultiTypeHint');
        $this->fixture->whenIMockAllOfItsProperties();

        $this->fixture->thenItsProperty_ShouldBeAnInstanceOf('foo', 'StdClass');

        $this->markTestIncomplete('Should not be injected');
        $this->fixture->thenItsProperty_ShouldBe('foo', null);
    }

    public function testMockMethodArguments() {
        $this->fixture->givenTheClassDefinition('
            class MethodWithDependencies {
                /**
                 * @param DateTime $one
                 * @param DateTime $two
                 */
                public function myFunction(StdClass $one, $two, $three) {
                    $this->one = $one;
                    $this->two = $two;
                    $this->three = $three;
                }
            }
        ');
        $this->fixture->whenICreateTheMockOf('MethodWithDependencies');
        $this->fixture->whenIUnMockTheMethod('myFunction');
        $this->fixture->whenIInvoke_OnTheMockWithTheArguments('myFunction', array('three' => 'foo'));

        $this->fixture->thenItsProperty_ShouldBeAnInstanceOf('one', 'StdClass');
        $this->fixture->thenItsProperty_ShouldBeAnInstanceOf('two', 'DateTime');
        $this->fixture->thenItsProperty_ShouldBe('three', 'foo');
    }

    public function testNotMockMethodOfInjectedMock() {
        $this->fixture->givenTheClassDefinition('
            class DependencyWithMethod {
                public function myFunction() {
                    $this->called = true;
                }
            }
        ');
        $this->fixture->givenTheClassDefinition('
            class InvokeDependency {
                /**
                 * @var DependencyWithMethod
                 */
                public $foo;
                public function myFunction() {
                    $this->called = true;
                    $this->foo->__mock()->method("myFunction")->dontMock();
                    $this->foo->myFunction();
                }
            }
        ');

        $this->fixture->whenICreateTheMockOf('InvokeDependency');
        $this->fixture->whenIMockAllOfItsProperties();

        $this->fixture->whenIUnMockTheMethod('myFunction');
        $this->fixture->whenIInvoke('myFunction');

        $this->fixture->thenItsProperty_ShouldBe('called', true);
        $this->fixture->thenItsProperty_OfProperty_ShouldBe('called', 'foo', true);
    }

}