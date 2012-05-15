<?php
namespace mockster;

class MocksterTest extends \PHPUnit_Framework_TestCase {

    public static $callbackInvoked;

    /**
     * @var MockFactory
     */
    private $factory;

    protected function setUp() {
        $this->factory = new MockFactory();
        self::$callbackInvoked = null;
        $this->background();
    }

    private function background() {

    }

    public function testMockPublicAndProtectedMethods() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(\mockster\TestMock1::CLASSNAME);
        $this->assertNotNull($mock);

        $mock->myPublicMethod();
        $mock->myProtectedMethod();

        $this->assertFalse($mock->publicInvoked);
        $this->assertFalse($mock->protectedInvoked);
    }

    public function testNoStubsForNotMockedMethods() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(\mockster\TestMock1::CLASSNAME);
        $this->assertNotNull($mock);

        $mock->__mock()->method('myPublicMethod')->dontMock();

        $mock->myPublicMethod();
        $mock->myProtectedMethod();

        $this->assertTrue($mock->publicInvoked);
        $this->assertFalse($mock->protectedInvoked);
    }

    public function testMethodStubRecordsCalls() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(\mockster\TestMock1::CLASSNAME);

        $mock->myPublicMethod();
        $mock->myPublicMethod(1, 'x');

        $stub = $mock->__mock()->method('myPublicMethod');
        $this->assertEquals(2, $stub->getCalledCount());
        $this->assertEquals(array(), $stub->getCalledArgumentsAt(0));
        $this->assertEquals(array(), $stub->getCalledArgumentsAt(-2));
        $this->assertEquals(array('arg1' => 1, 'arg2' => 'x'), $stub->getCalledArgumentsAt(1));
        $this->assertEquals('x', $stub->getCalledArgumentAt(1, 1));
        $this->assertEquals('x', $stub->getCalledArgumentAt(-1, 1));
        $this->assertEquals('x', $stub->getCalledArgumentAt(1, 'arg2'));
    }

    public function testMethodStubReturnValue() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(\mockster\TestMock1::CLASSNAME);

        $mock->__mock()->method('myPublicMethod')->willReturn('something');

        $this->assertEquals('something', $mock->myPublicMethod());
    }

    public function testReturnForCertainArguments() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(\mockster\TestMock1::CLASSNAME);

        $mock->__mock()->method('myPublicMethod')->willReturn('something')
                ->withArguments('myArg', 1);

        $this->assertNull($mock->myPublicMethod(0, 'notMyArgs'));
        $this->assertEquals('something', $mock->myPublicMethod('myArg', 1));
    }

    public function testMultipleSingleReturns() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(\mockster\TestMock1::CLASSNAME);

        $mock->__mock()->method('myPublicMethod')->willReturn('else')->once();
        $mock->__mock()->method('myPublicMethod')->willReturn('something')->once();

        $this->assertEquals('something', $mock->myPublicMethod());
        $this->assertEquals('else', $mock->myPublicMethod());
    }

    public function testMultipleSingleReturnsWithArguments() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(\mockster\TestMock1::CLASSNAME);

        $mock->__mock()->method('myPublicMethod')->willReturn('else')
                ->withArguments(1)->once();
        $mock->__mock()->method('myPublicMethod')->willReturn('two')
                ->withArguments(2)->once();
        $mock->__mock()->method('myPublicMethod')->willReturn('something')
                ->withArguments(1)->once();

        $this->assertNull($mock->myPublicMethod());

        $this->assertEquals('something', $mock->myPublicMethod(1));
        $this->assertEquals('else', $mock->myPublicMethod(1));
        $this->assertEquals('two', $mock->myPublicMethod(2));

        $this->assertNull($mock->myPublicMethod(1));
        $this->assertNull($mock->myPublicMethod(2));
    }

    public function testMethodStubCallback() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(\mockster\TestMock1::CLASSNAME);

        $mock->__mock()->method('myPublicMethod')->willCall(function ($arg1 = null, $arg2 = null) {
                    MocksterTest::$callbackInvoked = array($arg1, $arg2);
                });

        $mock->myPublicMethod();
        $mock->myPublicMethod(1, 'x');

        $this->assertEquals(array(1, 'x'), MocksterTest::$callbackInvoked);
    }

    public function testInjectInConstructor() {
        /** @var $mock Uut2 */
        $mock = $this->factory->createMock(\mockster\TestMock2::CLASSNAME, null);
        $mock->invokeInjected();

        $this->assertNull($mock->injected);

        $mock = $this->factory->createMock(\mockster\TestMock2::CLASSNAME);
        $mock->__mock()->method('invokeInjected')->dontMock();
        $mock->invokeInjected();

        $this->assertNotNull($mock->__mock()->getConstructorArgument('privateInjected'));
        $this->assertEquals(\mockster\TestMock1::CLASSNAME, get_parent_class($mock->__mock()->getConstructorArgument('privateInjected')));

        $this->assertNotNull($mock->injected);
        $this->assertFalse($mock->injected->publicInvoked);

        $stub = $mock->injected->__mock()->method('myPublicMethod');
        $this->assertEquals(1, $stub->getCalledCount());
        $this->assertEquals(array('arg1' => 'z'), $stub->getCalledArgumentsAt(0));
    }

    public function testNotMockMethodOfInjectedMock() {
        /** @var $mock Uut2 */
        $mock = $this->factory->createMock(\mockster\TestMock2::CLASSNAME);

        $mock->__mock()->method('invokeInjected')->dontMock();
        $mock->injected->__mock()->method('myPublicMethod')->dontMock();

        $mock->invokeInjected();

        $this->assertTrue($mock->injected->publicInvoked);
    }

    public function testInjectProperties() {
        /** @var $mock Uut3 */
        /** @var $mock TestMock3 */
        $mock = $this->factory->createMock(\mockster\TestMock3::CLASSNAME);

        $mock->__mock()->method('invokeIt')->dontMock();

        $mock->injected = $this->factory->createMock(\mockster\TestMock2::CLASSNAME);
        $mock->injected->__mock()->method('invokeInjected')->dontMock();
        $mock->injected->injected->__mock()->method('myPublicMethod')->dontMock();

        $mock->invokeIt();

        $this->assertNotNull($mock->injected);
        $this->assertTrue($mock->injected->injected->publicInvoked);
        $this->assertEquals(array(), $mock->anArray);
    }

    public function testInjectPropertiesWithMultipleTypehints() {
        /** @var $mock TestMock3|Uut3 */
        $mock = $this->factory->createMock(\mockster\TestMock3::CLASSNAME);

        $this->assertNotNull($mock->maybeInjected);
        $this->assertEquals(\mockster\TestMock1::CLASSNAME, get_parent_class($mock->maybeInjected));

        $this->assertNull($mock->notInjected);
    }

    public function testAccessPropertiesInjectedOverConstructor() {
        /** @var $mock Uut4 */
        $mock = $this->factory->createMock(\mockster\TestMock4::CLASSNAME);
        $this->assertNotNull($mock);

        $mock->__mock()->method('invokeInjected')->dontMock();

        $this->assertNotNull($mock->injected);
        $this->assertNotNull($mock->injected->publicInvoked);
    }

    public function testReturnMockOfReturnTypeHint() {
        /** @var $mock Uut4 */
        $mock = $this->factory->createMock(\mockster\TestMock4::CLASSNAME);

        $testMock = $mock->getTestMock();

        $this->assertNotNull($testMock);
        $name = 'Mock_TestMock1_';
        $this->assertEquals($name, substr(get_class($testMock), 0, strlen($name)));

        $this->assertEquals(array(), $mock->getArray());
        $this->assertEquals(array(), $mock->getArrayOrNull());
        $this->assertEquals(0, $mock->getInteger());
        $this->assertEquals(0.0, $mock->getFloat());
        $this->assertEquals(null, $mock->getNull());
        $this->assertEquals('', $mock->getString());
        $this->assertEquals(false, $mock->getBoolean());
    }

    public function testReturnValueForMethodChain() {
        /** @var $mock Uut4 */
        $mock = $this->factory->createMock(\mockster\TestMock4::CLASSNAME);

        $this->assertNull($mock->getTestMock3()->getTestMock2()->invokeInjected());

        $mock->__mock()->getChain('getTestMock3->getTestMock2->invokeInjected')
                ->willReturn('myValue');

        $this->assertNotNull($mock->getTestMock3());
        $this->assertEquals('myValue', $mock->getTestMock3()->getTestMock2()->invokeInjected());
    }

    public function testThrowException() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(\mockster\TestMock1::CLASSNAME);

        $mock->__mock()->method('myPublicMethod')->willThrow(new \Exception());

        try {
            $mock->myPublicMethod();
            $exception = false;
        } catch (\Exception $e) {
            $exception = true;
        }

        if (!$exception) {
            $this->fail('Should have thrown the excpetion.');
        }
    }

    public function testComplainWhenMethodDoesNotExist() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(\mockster\TestMock1::CLASSNAME);

        try {
            $mock->__mock()->method('nonExistingMethod');
            $this->fail('Should have thrown an exception.');
        } catch (\InvalidArgumentException $e) {
            // should throw since method does not exist
        }
    }

    public function testWasCalledWith() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(\mockster\TestMock1::CLASSNAME);

        $mock->myPublicMethod(1, 2);

        $this->assertTrue($mock->__mock()->method('myPublicMethod')->wasCalledWith(array(1, 2)));
        $this->assertTrue($mock->__mock()->method('myPublicMethod')->wasCalledWith(array('arg1' => 1, 'arg2' => 2)));
        $this->assertTrue($mock->__mock()->method('myPublicMethod')->wasCalledWith(array('arg2' => 2)));
        $this->assertTrue($mock->__mock()->method('myPublicMethod')->wasCalledWith(array('arg1' => 1)));
    }

    public function testStaticMethodIsMocked() {
        /** @var $mock StaticMock */
        $mock = $this->factory->createMock(\mockster\StaticMock::CLASSNAME);

        $mock->myStaticMethod();

        $this->assertFalse(StaticMock::$called);
    }

    public function testConstructorArguments() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(\mockster\TestMock1::CLASSNAME);

        $this->assertEquals(array(), $mock->array);
        $this->assertEquals(array(), $mock->__mock()->getConstructorArgument('array'));
        $this->assertEquals(null, $mock->mixed);
        $this->assertEquals(null, $mock->__mock()->getConstructorArgument('mixed'));
        $this->assertEquals(true, $mock->optional);
        $this->assertEquals(true, $mock->__mock()->getConstructorArgument('optional'));

        $this->assertNotNull($mock->inDoc);
        $this->assertTrue($mock->inDoc instanceof TestMock1);
        $this->assertTrue($mock->__mock()->getConstructorArgument('inDoc') instanceof TestMock1);

        $mock = $this->factory->createMock(\mockster\TestMock1::CLASSNAME, array('array' => array('one')));

        $this->assertEquals(array('one'), $mock->array);
        $this->assertEquals(null, $mock->mixed);
        $this->assertEquals(true, $mock->optional);

        $mock = $this->factory->createMock(\mockster\TestMock1::CLASSNAME, array(array('one'), 'two', null, false));

        $this->assertEquals(array('one'), $mock->array);
        $this->assertEquals('two', $mock->mixed);
        $this->assertEquals(false, $mock->optional);
    }

    public function testLogNotMockedMethodCalls() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(\mockster\TestMock1::CLASSNAME);

        $mock->__mock()->method('myPublicMethod')->dontMock();

        $mock->myPublicMethod('hey', 'ho');

        $this->assertEquals("Method: myPublicMethod\n  called: (hey, ho) => NOT MOCKED\n",
            $mock->__mock()->method('myPublicMethod')->getHistory());
    }

    public function testMockNoMethods() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(\mockster\TestMock1::CLASSNAME);

        $mock->__mock()->dontMockAllMethods();

        $mock->myPublicMethod();
        $mock->myProtectedMethod();

        $this->assertEquals(true, $mock->publicInvoked);
        $this->assertEquals(true, $mock->protectedInvoked);
    }

    public function testDontMockPublicMethods() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(\mockster\TestMock1::CLASSNAME);

        $mock->__mock()->dontMockPublicMethods();

        $mock->myPublicMethod();
        $mock->myProtectedMethod();

        $this->assertEquals(true, $mock->publicInvoked);
        $this->assertEquals(false, $mock->protectedInvoked);
    }

    public function testDontMockProtectedMethods() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(\mockster\TestMock1::CLASSNAME);

        $mock->__mock()->dontMockProtectedMethods();

        $mock->myPublicMethod();
        $mock->myProtectedMethod();

        $this->assertEquals(false, $mock->publicInvoked);
        $this->assertEquals(true, $mock->protectedInvoked);
    }

    public function testSingletons() {
        /** @var $mock1 Uut1 */
        $mock1 = $this->factory->createMock(\mockster\TestMock1::CLASSNAME);
        $this->factory->makeSingleton(\mockster\TestMock1::CLASSNAME, $mock1);

        /** @var $mock4 Uut4 */
        $mock4 = $this->factory->createMock(\mockster\TestMock4::CLASSNAME);
        /** @var $mock2 Uut2 */
        $mock2 = $this->factory->createMock(\mockster\TestMock2::CLASSNAME);

        $this->assertEquals($mock4->getTestMock(), $mock1);
        $this->assertEquals($mock4->getTestMock(), $this->factory->createMock(\mockster\TestMock1::CLASSNAME));
        $this->assertEquals($mock4->getTestMock(), $mock2->injected);

    }

    public function testInheritedMethods() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(\mockster\TestMock1::CLASSNAME);

        $mock->myPublicMethod();
        $mock->myProtectedMethod();

        $this->assertFalse($mock->publicInvoked);
        $this->assertFalse($mock->protectedInvoked);
    }

    public function testMockMethodArguments() {
        $mock = $this->factory->createMock(\mockster\TestMock4::CLASSNAME);
        $mock->__mock()->method('methodWithDependencies')->dontMock();
        list($mock1, $mock2, $mock3, $array, $int) = $mock->__mock()->invoke('methodWithDependencies', array('int' => 42));

        $this->assertTrue($mock1 instanceof TestMock1);
        $this->assertTrue($mock2 instanceof TestMock2);
        $this->assertNull($mock3);
        $this->assertEquals(array(), $array);
        $this->assertEquals(42, $int);
    }

}

/**
 * @method myProtectedMethod()
 * @method \mockster\Mockster __mock();
 */
class Uut1 extends TestMock1 {}

class TestMock1 {

    const CLASSNAME = __CLASS__;

    public $publicInvoked = false;
    public $protectedInvoked = false;

    public $array;
    public $mixed;
    public $optional;
    public $inDoc;

    public $arg1;
    public $arg2;

    /**
     * @param array $array
     * @param mixed $mixed
     * @param \mockster\TestMock1 $inDoc
     * @param bool $optional
     */
    public function __construct(array $array, $mixed, $inDoc, $optional = true) {
        $this->array = $array;
        $this->mixed = $mixed;
        $this->optional = $optional;
        $this->inDoc = $inDoc;
    }

    /**
     * @param null|mixed $arg1
     * @param null|mixed $arg2
     * @return mixed
     */
    public function myPublicMethod($arg1 = null, $arg2 = null) {
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
        $this->publicInvoked = true;
    }

    /**
     * @return mixed
     */
    protected function myProtectedMethod() {
        $this->protectedInvoked = true;
    }

}

class TestMock11 extends TestMock1 {
}

/**
 * @property \mockster\Mock|TestMock1 injected
 * @method \mockster\Mockster __mock()
 * @method \object invokeInjected()
 */
class Uut2 extends TestMock2 {}

class TestMock2 {

    const CLASSNAME = __CLASS__;

    protected $injected;
    private $dontTouch;

    public function __construct(TestMock1 $injected, TestMock1 $privateInjected) {
        $this->injected = $injected;
        $this->dontTouch = $privateInjected;
    }

    public function invokeInjected() {
        $this->injected->myPublicMethod("z");
    }

}

/**
 * @property \mockster\Mock|Uut2 injected
 * @property \mockster\Mock|Uut1 maybeInjected
 * @property \mockster\Mock|Uut1 notInjected
 * @property array anArray
 * @method \mockster\Mockster __mock()
 */
class Uut3 extends TestMock3 {}

class TestMock3 {

    const CLASSNAME = __CLASS__;

    /**
     * @var \mockster\TestMock2
     */
    protected $injected;

    /**
     * @var \mockster\TestMock1|null
     */
    public $maybeInjected;

    /**
     * @var null|\mockster\TestMock1
     */
    protected $notInjected;

    /**
     * @var string[]|array
     */
    protected $anArray;

    public function invokeIt() {
        $this->injected->invokeInjected();
    }

    /**
     * @return \mockster\TestMock2|Uut2
     */
    public function getTestMock2() { }

}

/**
 * @property \mockster\Mock|Uut1 injected
 * @method \mockster\Mockster __mock()
 */
class Uut4 extends TestMock4 {}

class TestMock4 {

    const CLASSNAME = __CLASS__;

    protected $injected;

    public function __construct(TestMock1 $injected) {
        $this->injected = $injected;
    }

    public function invokeInjected() {
        $this->injected->myPublicMethod("y");
    }

    /**
     * @param array $array
     * @param $int
     * @param TestMock1 $mock1
     * @param \mockster\TestMock2 $mock2
     * @param TestMock3|null $mock3
     * @return array
     */
    public function methodWithDependencies(array $array, $int, TestMock1 $mock1, $mock2, TestMock3 $mock3 = null) {
        return array($mock1, $mock2, $mock3, $array, $int);
    }

    /**
     * @return \mockster\TestMock1
     */
    public function getTestMock() { }

    /**
     * @return \mockster\TestMock3|Uut3
     */
    public function getTestMock3() { }

    /**
     * @return array
     */
    public function getArray() { }

    /**
     * @return array|null
     */
    public function getArrayOrNull() { }

    /**
     * @return int
     */
    public function getInteger() { }

    /**
     * @return float
     */
    public function getFloat() { }

    /**
     * @return null
     */
    public function getNull() { }

    /**
     * @return string
     */
    public function getString() { }

    /**
     * @return boolean
     */
    public function getBoolean() { }
}

class StaticMock {

    const CLASSNAME = __CLASS__;

    public static $called = false;

    public  static function myStaticMethod() {
        self::$called = true;
    }
}

?>
