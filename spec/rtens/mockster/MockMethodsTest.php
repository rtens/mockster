<?php
namespace spec\rtens\mockster;

use rtens\mockster\Mockster;
use watoki\scrut\Specification;

/**
 * @property MockFactoryFixture fixture <-
 */
class MockMethodsTest extends Specification {

    public function testMockPublicAndProtectedMethods() {
        $this->fixture->givenTheClassDefinition('
            class MockMethods {
                public $called = 0;
                public function myPublicFunction() {
                    $this->called++;
                }
                protected function myProtectedFunction() {
                    $this->called++;
                }
            }
        ');
        $this->fixture->whenICreateTheMockOf('MockMethods');
        $this->fixture->whenIInvoke('myPublicFunction');
        $this->fixture->whenIInvoke('myProtectedFunction');

        $this->fixture->thenItsProperty_ShouldBe('called', 0);
    }

    public function testUnMockMethods() {
        $this->fixture->givenTheClassDefinition('
            class UnMockMethods {
                public $called = 0;
                public function myPublicFunction() {
                    $this->called++;
                }
                protected function myProtectedFunction() {
                    $this->called++;
                }
            }
        ');
        $this->fixture->whenICreateTheMockOf('UnMockMethods');
        $this->fixture->whenIUnMockTheMethod('myPublicFunction');
        $this->fixture->whenIUnMockTheMethod('myProtectedFunction');

        $this->fixture->whenIInvoke('myPublicFunction');
        $this->fixture->whenIInvoke('myProtectedFunction');

        $this->fixture->thenItsProperty_ShouldBe('called', 2);
    }

    public function testComplainWhenMethodDoesNotExist() {
        $this->fixture->givenTheClassDefinition('
            class NoMethodHere {}
        ');
        $this->fixture->whenICreateTheMockOf('NoMethodHere');
        $this->fixture->whenITryToAccessTheMethod('notExisting');

        $this->fixture->thenAnExceptionShouldBeThrownContaining("Can't mock method NoMethodHere::notExisting.");
    }

    public function testComplainWhenMethodIsStatic() {
        $this->fixture->givenTheClassDefinition('
            class HasAStaticMethod {
                public static function foo() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('HasAStaticMethod');
        $this->fixture->whenITryToAccessTheMethod('foo');

        $this->fixture->thenAnExceptionShouldBeThrownContaining("Can't mock method HasAStaticMethod::foo.");
    }

    public function testComplainWhenMethodIsPrivate() {
        $this->fixture->givenTheClassDefinition('
            class HasAPrivateMethod {
                private function foo() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('HasAPrivateMethod');
        $this->fixture->whenITryToAccessTheMethod('foo');

        $this->fixture->thenAnExceptionShouldBeThrownContaining("Can't mock method HasAPrivateMethod::foo.");
    }

    public function testMockNoMethods() {
        $this->fixture->givenTheClassDefinition('
            class MockNoMethods {
                public $called = 0;
                public function myPublicFunction() {
                    $this->called++;
                }
                protected function myProtectedFunction() {
                    $this->called++;
                }
            }
        ');
        $this->fixture->whenICreateTheMockOf('UnMockMethods');
        $this->fixture->whenIMockAllMethodsMatching(Mockster::F_NONE);

        $this->fixture->whenIInvoke('myPublicFunction');
        $this->fixture->whenIInvoke('myProtectedFunction');

        $this->fixture->thenItsProperty_ShouldBe('called', 2);
    }

    public function testDontMockPublicMethods() {
        $this->fixture->givenTheClassDefinition('
            class DontMockPublicMethods {
                public $called = 0;
                public function myPublicFunction() {
                    $this->called++;
                }
                protected function myProtectedFunction() {
                    $this->called++;
                }
                private function myPrivateFunction() {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('DontMockPublicMethods');
        $this->fixture->whenIMockAllMethodsMatching(~Mockster::F_PUBLIC);

        $this->fixture->whenIInvoke('myPublicFunction');
        $this->fixture->thenItsProperty_ShouldBe('called', 1);

        $this->fixture->whenIInvoke('myProtectedFunction');
        $this->fixture->thenItsProperty_ShouldBe('called', 1);
    }

    public function testDontMockProtectedMethods() {
        $this->fixture->givenTheClassDefinition('
            class DontMockProtectedMethods {
                public $called = 0;
                public function myPublicFunction() {
                    $this->called++;
                }
                protected function myProtectedFunction() {
                    $this->called++;
                }
            }
        ');
        $this->fixture->whenICreateTheMockOf('DontMockProtectedMethods');
        $this->fixture->whenIMockAllMethodsMatching(~Mockster::F_PROTECTED);

        $this->fixture->whenIInvoke('myPublicFunction');
        $this->fixture->thenItsProperty_ShouldBe('called', 0);

        $this->fixture->whenIInvoke('myProtectedFunction');
        $this->fixture->thenItsProperty_ShouldBe('called', 1);
    }

    public function testMockOnlyAnnotatedMethods() {
        $this->fixture->givenTheClassDefinition('
            class MockOnlyAnnotatedMethods {
                public $called = 0;

                /** @mockThis */
                public function functionOne() {
                    $this->called++;
                }

                /** @notThis */
                public function functionTwo() {
                    $this->called++;
                }
            }
        ');
        $this->fixture->whenICreateTheMockOf('MockOnlyAnnotatedMethods');
        $this->fixture->whenIMockAllMethodsMatching_WithAnnotation(Mockster::F_ALL, '@mockThis');

        $this->fixture->whenIInvoke('functionOne');
        $this->fixture->thenItsProperty_ShouldBe('called', 0);

        $this->fixture->whenIInvoke('functionTwo');
        $this->fixture->thenItsProperty_ShouldBe('called', 1);
    }

    public function testInheritedMethods() {
        $this->fixture->givenTheClassDefinition('
            class BaseClass {
                public $called = 0;
                protected function myFunction() {
                    $this->called++;
                }
            }
        ');
        $this->fixture->givenTheClassDefinition('
            class SubClass extends BaseClass {
                protected function yourFunction() {
                    $this->called++;
                }
            }
        ');

        $this->fixture->whenICreateTheMockOf('SubClass');
        $this->fixture->whenIInvoke('myFunction');
        $this->fixture->whenIInvoke('yourFunction');

        $this->fixture->thenItsProperty_ShouldBe('called', 0);
    }

    public function testMockDefaultValues() {
        $this->fixture->givenTheClassDefinition('
            class DefaultValues {
                public function myFunction($one = "one", $two = 2, $three = true) {
                }
            }
        ');

        $this->fixture->whenICreateTheMockOf('DefaultValues');
        $this->fixture->whenIInvoke('myFunction');
    }

    public function testMethodIterator() {
        $this->fixture->givenTheClassDefinition('
            class MethodIterator {
                public $called = 0;
                public function myPublicFunction() {
                    $this->called++;
                }
                protected function myProtectedFunction() {
                    $this->called++;
                }
            }
        ');
        $this->fixture->whenICreateTheMockOf('MethodIterator');
        $this->fixture->whenIInvokeAllMethods();
        $this->fixture->thenItsProperty_ShouldBe('called', 0);
    }

}