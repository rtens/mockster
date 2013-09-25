<?php
namespace spec\rtens\mockster;

use watoki\scrut\Specification;

/**
 * @property MockFactoryFixture fixture <-
 */
class CreateMockTest extends Specification {

    public function testMockWithoutConstructor() {
        $this->fixture->givenTheClassDefinition('
            class SkipThisConstructor {
                public $called = false;
                public function __construct() {
                    $this->called = true;
                }
            }
        ');
        $this->fixture->whenICreateTheMockOf('SkipThisConstructor');

        $this->fixture->thenItsProperty_ShouldBe('called', false);
        $this->fixture->thenTheMockShouldBeAnInstanceOf('rtens\mockster\Mock');
    }

    public function testCallConstructor() {
        $this->fixture->givenTheClassDefinition('
            class CallEmptyConstructor {
                public $called = false;
                public function __construct() {
                    $this->called = true;
                }
            }
        ');
        $this->fixture->whenICreateTheMockOf_WithTheConstructorArguments('CallEmptyConstructor', array());

        $this->fixture->thenItsProperty_ShouldBe('called', true);
    }

    public function testConstructorArguments() {
        $this->fixture->givenTheClassDefinition('
            class ClassWithConstructorArguments {
                public $one;
                public $two;
                public $three;
                public function __construct($one, $two, $three = "three") {
                    $this->one = $one;
                    $this->two = $two;
                    $this->three = $three;
                }
            }
        ');

        $this->fixture->whenICreateTheMockOf_WithTheConstructorArguments('ClassWithConstructorArguments', array(1, 2));
        $this->fixture->thenItsProperty_ShouldBe('one', 1);
        $this->fixture->thenItsProperty_ShouldBe('two', 2);
        $this->fixture->thenItsProperty_ShouldBe('three', 'three');

        $this->fixture->thenTheConstructorArgument_ShouldBe('one', 1);
        $this->fixture->thenTheConstructorArgument_ShouldBe(1, 2);

        $this->fixture->whenICreateTheMockOf_WithTheConstructorArguments('ClassWithConstructorArguments',
            array('two' => 1, 'one' => 2));
        $this->fixture->thenItsProperty_ShouldBe('one', 2);
        $this->fixture->thenItsProperty_ShouldBe('two', 1);
    }

    public function testMockInterface() {
        $this->fixture->givenTheClassDefinition('
            interface MyInterface {
                public function myFunction();
            }
        ');
        $this->fixture->whenICreateTheMockOf('MyInterface');

        $this->fixture->whenIInvoke('myFunction');
        $this->fixture->thenTheMockShouldBeAnInstanceOf('MyInterface');
    }

    public function testMockAbstractClass() {
        $this->fixture->givenTheClassDefinition('
            abstract class MyAbstractClass {
                abstract function myFunction();
            }
        ');
        $this->fixture->whenICreateTheMockOf('MyAbstractClass');
        $this->fixture->whenIInvoke('myFunction');
        $this->fixture->thenTheMockShouldBeAnInstanceOf('MyAbstractClass');
    }

    public function testDontTryToMockMethodsCalledInConstructor() {
        $this->fixture->givenTheClassDefinition('
            class CallMethodInConstructor {
                public $called = false;
                public function __construct() {
                    $this->myFunction();
                }
                public function myFunction() {
                    $this->called = true;
                }
            }
        ');
        $this->fixture->whenICreateTheMockOf_WithTheConstructorArguments('CallMethodInConstructor', array());
        $this->fixture->thenItsProperty_ShouldBe('called', true);
    }

    public function testStaticMethodIsNotMocked() {
        $this->fixture->givenTheClassDefinition('
            class StaticMethod {
                public static $called = false;
                public static function myMethod() {
                    self::$called = true;
                }
            }
        ');
        $this->fixture->whenICreateTheMockOf('StaticMethod');
        $this->fixture->whenIInvoke('myMethod');

        $this->fixture->thenItsStaticProperty_ShouldBe('called', true);
    }

    public function testTestUnit() {
        $this->fixture->givenTheClassDefinition('
            class TestUnit {
                /** @var StdClass */
                protected $bar;
                public $foo;
                public function doFoo() {
                    $this->foo = "foo";
                }
            }
        ');
        $this->fixture->whenICreateATestUnitOf('TestUnit');
        $this->fixture->whenIInvoke('doFoo');

        $this->fixture->thenItsProperty_ShouldBe('foo', 'foo');
        $this->fixture->thenItsProperty_ShouldBeAnInstanceOf('bar', 'StdClass');
        $this->fixture->thenItsProperty_ShouldBeAnInstanceOf('bar', 'rtens\mockster\Mock');
    }

}