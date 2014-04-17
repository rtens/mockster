<?php
namespace spec\rtens\mockster;

use rtens\mockster\Mock;
use rtens\mockster\MockFactory;
use rtens\mockster\Mockster;
use watoki\factory\Factory;
use watoki\scrut\Fixture;
use watoki\scrut\Specification;

class MockFactoryFixture extends Fixture {

    public static $calledWith;

    /** @var null|\Exception */
    private $caught;

    /** @var Mock */
    private $mock;

    private $returnValue;

    private static $counter = 0;

    public function __construct(Specification $spec, Factory $factory) {
        parent::__construct($spec, $factory);
    }

    public function givenTheClassDefinition($string) {
        $file = __DIR__ . '/tmp/class' . self::$counter++ . '.php';
        @mkdir(dirname($file));
        file_put_contents($file, "<?php $string");
        /** @noinspection PhpIncludeInspection */
        include $file;
        $this->spec->undos[] = function () use ($file) {
            @unlink($file);
            @rmdir(dirname($file));
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
        $this->spec->assertEquals($string, $this->returnValue);
    }

    public function thenItsProperty_ShouldBe($property, $value) {
        $this->spec->assertEquals($value, @$this->mock->$property);
    }

    public function thenItsProperty_ShouldBeAnInstanceOf($property, $class) {
        $this->spec->assertInstanceOf($class, $this->mock->__mock()->get($property));
    }

    public function thenItsProperty_ShouldNotBeAnInstanceOf($property, $class) {
        $this->spec->assertNotInstanceOf($class, $this->mock->__mock()->get($property));
    }

    public function thenItsProperty_OfProperty_ShouldBe($innerProperty, $property, $class) {
        $this->spec->assertEquals($class, $this->mock->__mock()->get($property)->__mock()->get($innerProperty));
    }

    public function thenTheConstructorArgument_ShouldBe($name, $value) {
        $this->spec->assertEquals($value, $this->mock->__mock()->getConstructorArgument($name));
    }

    public function thenTheMockShouldBeAnInstanceOf($class) {
        $this->spec->assertInstanceOf($class, $this->mock);
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
        $this->spec->assertNotNull($this->caught, "No exception containing [$message] was caught.");
        $this->spec->assertContains($message, $this->caught->getMessage());
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

    public function thenTheCallbackShouldBeCalledWith($string) {
        $this->spec->assertEquals($string, self::$calledWith);
    }

    public function whenIConfigureTheMethod_ToThrowAnExceptionWithTheMessage($method, $message) {
        $this->mock->__mock()->method($method)->willThrow(new \Exception($message));
    }

    public function thenTheCallCountOf_ShouldBe($method, $count) {
        $this->spec->assertEquals($count, $this->mock->__mock()->method($method)->getHistory()->getCalledCount());
    }

    public function thenTheArgumentsOfCallIndex_OfMethod_ShouldBe($index, $method, $args) {
        $this->spec->assertEquals($args, $this->mock->__mock()->method($method)->getHistory()->getCalledArgumentsAt($index));
    }

    public function thenTheArgument_OfCallIndex_OfMethod_ShouldBe($argIndex, $methodIndex, $method, $value) {
        $this->spec->assertEquals($value, $this->mock->__mock()->method($method)->getHistory()
            ->getCalledArgumentAt($methodIndex, $argIndex));
    }

    public function thenTheCalledArgumentsOf_ShouldBe($method, $array) {
        $this->spec->assertEquals($array, $this->mock->__mock()->method($method)->getHistory()->getCalledArguments());
    }

    public function thenTheMethod_WasCalledWith($method, $array) {
        $this->spec->assertTrue($this->mock->__mock()->method($method)->getHistory()->wasCalledWith($array),
            "Not called with " . json_encode($array));
    }

    public function thenTheHistoryShouldBe($history) {
        $this->then_ShouldBe($history, $this->mock->__mock()->getHistory());
    }

    public function thenTheHistoryOf_ShouldBe($method, $history) {
        $this->then_ShouldBe($history, $this->mock->__mock()->method($method)->getHistory());
    }

    private function then_ShouldBe($string1, $string2) {
        $this->spec->assertEquals(str_replace(array("  ", "\t", "\r"), array(' ', ' ', ''), $string1),
            str_replace(array("  ", "\t", "\r"), array(' ', ' ', ''), $string2));
    }

    public function thenTheInjectedArgument_ShouldBeAnInstanceOf($arg, $class) {
        $this->spec->assertInstanceOf($class, $this->mock->__mock()->getConstructorArgument($arg));
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
        $this->spec->assertInstanceOf($string, $this->returnValue);
    }

    public function whenIInvokeTheChain($chain) {
        eval("\$this->returnValue = \$this->mock->$chain;");
    }

    public function whenIConfigureTheChain_ToReturn($chain, $return) {
        $this->mock->__mock()->getChain($chain)->willReturn($return);
    }

    public function thenItsStaticProperty_ShouldBe($property, $value) {
        $mockClass = get_class($this->mock);
        $this->spec->assertEquals($value, $mockClass::${$property});
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

}