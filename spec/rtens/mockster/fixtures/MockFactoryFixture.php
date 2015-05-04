<?php
namespace spec\rtens\mockster\fixtures;

use rtens\mockster\MockFactory;
use rtens\mockster\Mockster;
use rtens\scrut\Assert;

/**
 * @property Assert $assert <-
 */
class MockFactoryFixture extends Fixture {

    public static $calledWith;

    /** @var null|\Exception */
    private $caught;

    /** @var \rtens\mockster\Mock */
    private $mock;

    private $returnValue;

    private static $counter = 0;

    public function givenTheClassDefinition($string) {
        $file = __DIR__ . '/tmp/class' . self::$counter++ . '.php';
        @mkdir(dirname($file));
        file_put_contents($file, "<?php $string");

        /** @noinspection PhpIncludeInspection */
        include $file;

        $this->undos[] = function () use ($file) {
            @unlink($file);
        };
    }

    public function whenICreateTheMockOf($class) {
        $factory = new MockFactory();
        $this->mock = $factory->getInstance($class);
    }

    public function whenICreateTheMockOf_WithTheConstructorArguments($class, $args) {
        $factory = new MockFactory();
        $this->mock = $factory->getInstance($class, $args);
    }

    public function whenICreateATestUnitOf($class) {
        $factory = new MockFactory();
        $this->mock = $factory->getInstance($class)->__mock()->makeTestUnit();
    }

    public function whenITryToInvoke($method) {
        try {
            $this->whenIInvoke($method);
        } catch (\Exception $e) {
            $this->caught = $e;
        }
    }

    public function whenIInvoke($method) {
        $this->returnValue = call_user_func(array($this->mock, $method));
    }

    public function whenIInvokeAllMethods() {
        foreach ($this->mock->__mock()->methods() as $method) {
            /* @var $method \rtens\mockster\Method */
            call_user_func(array($this->mock, $method->getName()));
        }
    }

    public function whenIInvoke_WithTheArgument($method, $argument) {
        $this->returnValue = call_user_func(array($this->mock, $method), $argument);
    }

    public function whenIInvoke_WithTheArgument_And($method, $arg1, $arg2) {
        $this->returnValue = call_user_func(array($this->mock, $method), $arg1, $arg2);
    }

    public function whenIInvoke_WithTheArguments__And($method, $arg1, $arg2, $arg3) {
        $this->returnValue = call_user_func(array($this->mock, $method), $arg1, $arg2, $arg3);
    }

    public function thenItShouldReturn($string) {
        $this->assert->equals($this->returnValue, $string);
    }

    public function thenItsProperty_ShouldBe($property, $value) {
        $this->assert->equals(@$this->mock->$property, $value);
    }

    public function thenItsProperty_ShouldBeAnInstanceOf($property, $class) {
        $this->assert->isInstanceOf($this->mock->__mock()->get($property), $class);
    }

    public function thenItsProperty_ShouldNotBeAnInstanceOf($property, $class) {
        $this->assert->not()->isInstanceOf($this->mock->__mock()->get($property), $class);
    }

    public function thenItsProperty_OfProperty_ShouldBe($innerProperty, $property, $class) {
        $this->assert->equals($this->mock->__mock()->get($property)->__mock()->get($innerProperty), $class);
    }

    public function thenTheConstructorArgument_ShouldBe($name, $value) {
        $this->assert->equals($this->mock->__mock()->getConstructorArgument($name), $value);
    }

    public function thenTheMockShouldBeAnInstanceOf($class) {
        $this->assert->isInstanceOf($this->mock, $class);
    }

    public function whenIUnMockTheMethod($method) {
        $this->mock->__mock()->method($method)->dontMock();
    }

    public function whenITryToAccessTheMethod($method) {
        try {
            $this->mock->__mock()->method($method);
        } catch (\Exception $e) {
            $this->caught = $e;
        }
    }

    public function thenAnExceptionShouldBeThrownContaining($message) {
        $this->assert->not()->isNull($this->caught, "No exception containing [$message] was caught.");
        $this->assert->contains($this->caught->getMessage(), $message);
    }

    public function thenNoExceptionShouldBeThrown() {
        $this->assert->isTrue($this->caught === null, "Caught something: "
            . (is_object($this->caught) ? $this->caught->getMessage() : print_r($this->caught, true)));
    }

    public function whenIMockAllMethodsMatching($filter) {
        $this->mock->__mock()->mockMethods($filter);
    }

    public function whenIMockAllMethodsMatching_WithAnnotation($filter, $annotation) {
        $this->mock->__mock()->mockMethods($filter, function (\ReflectionMethod $method) use ($annotation) {
            return strpos($method->getDocComment(), $annotation) !== false;
        });
    }

    public function whenIConfigureTheMethod_ToReturn($method, $return) {
        $this->mock->__mock()->method($method)->willReturn($return);
    }

    public function whenIConfigureTheMethod_ToReturnAMockOf($method, $class) {
        $factory = new MockFactory();
        $this->mock->__mock()->method($method)->willReturn($factory->getInstance($class));
    }

    public function whenIConfigureTheMethod_ToReturn_WhenCalledWithTheArgument($method, $return, $arg) {
        $this->mock->__mock()->method($method)->willReturn($return)->withArguments($arg);
    }

    public function whenIConfigureTheMethod_ToReturn_OnceWhenCalledWithTheArgument($method, $return, $arg) {
        $this->mock->__mock()->method($method)->willReturn($return)->withArguments($arg)->once();
    }

    public function whenIConfigureTheMethod_ToReturn_WhenCalledWith($method, $return, $args) {
        $this->mock->__mock()->method($method)->willReturn($return)->with($args);
    }

    public function whenIConfigureTheMethod_ToReturn_Once($method, $return) {
        $this->mock->__mock()->method($method)->willReturn($return)->once();
    }

    public function whenIConfigureTheMethod_ToCallAClosure($method) {
        $this->mock->__mock()->method($method)->willCall(function ($arg) {
            MockFactoryFixture::$calledWith = $arg;
            return $arg;
        });
    }

    public function whenIDisableTheReturnTypeHintCheckForTheMethod($method) {
        $this->mock->__mock()->method($method)->dontCheckReturnType();
    }

    public function thenTheCallbackShouldBeCalledWith($string) {
        $this->assert->equals(self::$calledWith, $string);
    }

    public function whenIConfigureTheMethod_ToThrowAnExceptionWithTheMessage($method, $message) {
        $this->mock->__mock()->method($method)->willThrow(new \Exception($message));
    }

    public function thenTheCallCountOf_ShouldBe($method, $count) {
        $this->assert->equals($this->mock->__mock()->method($method)->getHistory()->getCalledCount(), $count);
    }

    public function thenTheArgumentsOfCallIndex_OfMethod_ShouldBe($index, $method, $args) {
        $this->assert->equals($this->mock->__mock()->method($method)->getHistory()->getCalledArgumentsAt($index), $args);
    }

    public function thenTheArgument_OfCallIndex_OfMethod_ShouldBe($argIndex, $methodIndex, $method, $value) {
        $this->assert->equals($this->mock->__mock()->method($method)->getHistory()
            ->getCalledArgumentAt($methodIndex, $argIndex), $value);
    }

    public function thenTheCalledArgumentsOf_ShouldBe($method, $array) {
        $this->assert->equals($this->mock->__mock()->method($method)->getHistory()->getCalledArguments(), $array);
    }

    public function thenTheMethod_WasCalledWith($method, $array) {
        $this->assert->isTrue($this->mock->__mock()->method($method)->getHistory()->wasCalledWith($array),
            "Not called with " . json_encode($array));
    }

    public function thenTheHistoryShouldBe($history) {
        $this->then_ShouldBe($history, $this->mock->__mock()->getHistory());
    }

    public function thenTheHistoryOf_ShouldBe($method, $history) {
        $this->then_ShouldBe($history, $this->mock->__mock()->method($method)->getHistory());
    }

    private function then_ShouldBe($string1, $string2) {
        $this->assert->equals(str_replace(array("  ", "\t", "\r"), array(' ', ' ', ''), $string2), str_replace(array("  ", "\t", "\r"), array(' ', ' ', ''), $string1));
    }

    public function thenTheInjectedArgument_ShouldBeAnInstanceOf($arg, $class) {
        $this->assert->isInstanceOf($this->mock->__mock()->getConstructorArgument($arg), $class);
    }

    public function whenIMockAllMarkedProperties() {
        $this->mock->__mock()->mockProperties(Mockster::F_ALL, function (\ReflectionProperty $prop) {
            return strpos($prop->getDocComment(), '<-') !== false;
        });
    }

    public function whenIMockAllOfItsPropertiesAnnotatedWith($string) {
        $this->mock->__mock()->mockProperties(Mockster::F_ALL, function (\ReflectionProperty $property) use ($string) {
            return strpos($property->getDocComment(), $string) !== false;
        });
    }

    public function whenIMockItsProtectedProperties() {
        $this->mock->__mock()->mockProperties(Mockster::F_PROTECTED);
    }

    public function whenIMockAllItsProperties() {
        $this->mock->__mock()->mockProperties(Mockster::F_ALL);
    }

    public function whenIInvoke_OnTheMockWithTheArguments($method, $args) {
        $this->mock->__mock()->invoke($method, $args);
    }

    public function thenItShouldReturnAnInstanceOf($string) {
        $this->assert->isInstanceOf($this->returnValue, $string);
    }

    public function whenIInvokeTheChain($chain) {
        eval("\$this->returnValue = \$this->mock->$chain;");
    }

    public function whenIConfigureTheChain_ToReturn($chain, $return) {
        $this->mock->__mock()->getChain($chain)->willReturn($return);
    }

    public function thenItsStaticProperty_ShouldBe($property, $value) {
        $mockClass = get_class($this->mock);
        $this->assert->equals($mockClass::${$property}, $value);
    }

    public function whenITryToMockAllMarkedProperties() {
        try {
            $this->whenIMockAllMarkedProperties();
        } catch (\Exception $e) {
            $this->caught = $e;
        }
    }

    public function whenIConfigureTheMethod_ToReturn_WhenTheArgumentIsBetween_And($method, $return, $low, $high) {
        $this->mock->__mock()->method($method)->willReturn($return)->when(function ($arg) use ($low, $high) {
            return $arg > $low && $arg < $high;
        });
    }

    public function whenITryToCreateTheMockOf($className) {
        try {
            $this->whenICreateTheMockOf($className);
        } catch (\Exception $e) {
            $this->caught = $e;
        }
    }

}
