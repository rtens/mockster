<?php
namespace spec\rtens\mockster;

use rtens\mockster\MockFactory;
use rtens\mockster\Mock;
use rtens\mockster\Mockster;

class MocksterTest extends \PHPUnit_Framework_TestCase {

    public static $callbackInvoked;

    /**
     * @var \rtens\mockster\MockFactory
     */
    private $factory;

    protected function setUp() {
        $this->factory = new MockFactory();
        self::$callbackInvoked = null;
    }

    public function testMockProxyGeneration() {
        $mock = $this->factory->createMock(TestMock1::CLASSNAME);

        $generated = <<<EOD
1:
2: class Mock_TestMock1_f83333f8  extends spec\\rtens\\mockster\\TestMock1 implements \\rtens\\mockster\\Mock {
3:
4:     private \$__mock;
5:     public static \$__mockInstance;
6:
7:
8:
9:     public function __mock() {
10:         return \$this->__mock;
11:     }
12:
13:     public function __construct() {}
14:
15:
16:
17:     /**
18:      * @param null|mixed \$arg1
19:      * @param null|mixed \$arg2
20:      * @return mixed
21:      */
22:     public  function myPublicMethod ( \$arg1 = NULL, \$arg2 = NULL ) {
23:         if (!\$this->__mock()) {
24:             return parent::myPublicMethod( \$arg1, \$arg2 );
25:         }
26:
27:         \$method = \$this->__mock()->method('myPublicMethod');
28:
29:         if (false || \$method->isMocked()) {
30:             return \$method->invoke(func_get_args());
31:         } else {
32:             \$method->log(func_get_args());
33:             return parent::myPublicMethod( \$arg1, \$arg2 );
34:         }
35:     }
36:
37:     /**
38:      * @return mixed
39:      */
40:     public  function myProtectedMethod (  ) {
41:         if (!\$this->__mock()) {
42:             return parent::myProtectedMethod(  );
43:         }
44:
45:         \$method = \$this->__mock()->method('myProtectedMethod');
46:
47:         if (false || \$method->isMocked()) {
48:             return \$method->invoke(func_get_args());
49:         } else {
50:             \$method->log(func_get_args());
51:             return parent::myProtectedMethod(  );
52:         }
53:     }
54:
55:     /**
56:      * @mockIt
57:      */
58:     public static function myStaticMethod (  ) {
59:         if (!self::\$__mockInstance->__mock()) {
60:             return parent::myStaticMethod(  );
61:         }
62:
63:         \$method = self::\$__mockInstance->__mock()->method('myStaticMethod');
64:
65:         if (false || \$method->isMocked()) {
66:             return \$method->invoke(func_get_args());
67:         } else {
68:             \$method->log(func_get_args());
69:             return parent::myStaticMethod(  );
70:         }
71:     }
72:
73:     /**
74:      * @mockIt
75:      */
76:     public  function myMockedMethod (  ) {
77:         if (!\$this->__mock()) {
78:             return parent::myMockedMethod(  );
79:         }
80:
81:         \$method = \$this->__mock()->method('myMockedMethod');
82:
83:         if (false || \$method->isMocked()) {
84:             return \$method->invoke(func_get_args());
85:         } else {
86:             \$method->log(func_get_args());
87:             return parent::myMockedMethod(  );
88:         }
89:     }
90:
91: }

EOD;

        $this->assertEquals(preg_replace('/\s+\n/', "\n", substr($generated, 35)),
            preg_replace('/\s+\n/', "\n", substr($mock->__mock()->getCode(), 36)));
    }

    public function testMockPublicAndProtectedMethods() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(TestMock1::CLASSNAME);
        $this->assertNotNull($mock);

        $mock->myPublicMethod();
        $mock->myProtectedMethod();

        $this->assertFalse($mock->publicInvoked);
        $this->assertFalse($mock->protectedInvoked);
    }

    public function testNoStubsForNotMockedMethods() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(TestMock1::CLASSNAME);
        $this->assertNotNull($mock);

        $mock->__mock()->method('myPublicMethod')->dontMock();

        $mock->myPublicMethod();
        $mock->myProtectedMethod();

        $this->assertTrue($mock->publicInvoked);
        $this->assertFalse($mock->protectedInvoked);
    }

    public function testImplicitlyMockMethodWhenBehaviourDefined() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(TestMock1::CLASSNAME);
        $mock->__mock()->mockMethods(Mockster::F_NONE);

        $mock->__mock()->method('myPublicMethod')->willReturn('nothing');

        $returned = $mock->myPublicMethod();

        $this->assertEquals('nothing', $returned);
        $this->assertFalse($mock->publicInvoked);
    }

    public function testMethodStubRecordsCalls() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(TestMock1::CLASSNAME);

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

    public function testFalseAndNullArgumentsAreRecorded() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(TestMock1::CLASSNAME);

        $mock->myPublicMethod(null, false);

        $stub = $mock->__mock()->method('myPublicMethod');
        $this->assertEquals(1, $stub->getCalledCount());
        $this->assertTrue($stub->wasCalledWith(array(null, false)));
    }

    public function testMethodStubReturnValue() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(TestMock1::CLASSNAME);

        $mock->__mock()->method('myPublicMethod')->willReturn('something');

        $this->assertEquals('something', $mock->myPublicMethod());
    }

    public function testReturnForCertainArguments() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(TestMock1::CLASSNAME);

        $mock->__mock()->method('myPublicMethod')->willReturn('something')
                ->withArguments('myArg', 1);

        $this->assertNull($mock->myPublicMethod(0, 'notMyArgs'));
        $this->assertEquals('something', $mock->myPublicMethod('myArg', 1));
    }

    public function testMultipleSingleReturns() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(TestMock1::CLASSNAME);

        $mock->__mock()->method('myPublicMethod')->willReturn('else')->once();
        $mock->__mock()->method('myPublicMethod')->willReturn('something')->once();

        $this->assertEquals('something', $mock->myPublicMethod());
        $this->assertEquals('else', $mock->myPublicMethod());
    }

    public function testMultipleSingleReturnsWithArguments() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(TestMock1::CLASSNAME);

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
        $mock = $this->factory->createMock(TestMock1::CLASSNAME);

        $mock->__mock()->method('myPublicMethod')->willCall(function ($arg1 = null, $arg2 = null) {
                    MocksterTest::$callbackInvoked = array($arg1, $arg2);
                });

        $mock->myPublicMethod();
        $mock->myPublicMethod(1, 'x');

        $this->assertEquals(array(1, 'x'), MocksterTest::$callbackInvoked);
    }

    public function testInjectInConstructor() {
        /** @var $mock Uut2 */
        $mock = $this->factory->createMock(TestMock2::CLASSNAME);
        $mock->__mock()->mockProperties();
        $mock->invokeInjected();

        $this->assertNull($mock->injected);

        $mock = $this->factory->createMock(TestMock2::CLASSNAME, array());
        $mock->__mock()->mockProperties();
        $mock->__mock()->method('invokeInjected')->dontMock();
        $mock->invokeInjected();

        $this->assertNotNull($mock->__mock()->getConstructorArgument('privateInjected'));
        $this->assertEquals(TestMock1::CLASSNAME, get_parent_class($mock->__mock()->getConstructorArgument(0)));

        $this->assertNotNull($mock->injected);
        $this->assertFalse($mock->injected->publicInvoked);

        $stub = $mock->injected->__mock()->method('myPublicMethod');
        $this->assertEquals(1, $stub->getCalledCount());
        $this->assertEquals(array('arg1' => 'z'), $stub->getCalledArgumentsAt(0));
    }

    public function testNotMockMethodOfInjectedMock() {
        /** @var $mock Uut2 */
        $mock = $this->factory->createMock(TestMock2::CLASSNAME, array());
        $mock->__mock()->mockProperties();

        $mock->__mock()->method('invokeInjected')->dontMock();
        $mock->injected->__mock()->method('myPublicMethod')->dontMock();

        $mock->invokeInjected();

        $this->assertTrue($mock->injected->publicInvoked);
    }

    public function testInjectProperties() {
        /** @var $mock TestMock3|Uut3 */
        $mock = $this->factory->createMock(TestMock3::CLASSNAME);
        $mock->__mock()->mockProperties();

        $mock->__mock()->method('invokeIt')->dontMock();

        $mock->injected = $this->factory->createMock(TestMock2::CLASSNAME, array(), true);
        $mock->injected->__mock()->method('invokeInjected')->dontMock();
        $mock->injected->injected->__mock()->method('myPublicMethod')->dontMock();

        $mock->invokeIt();

        $this->assertNotNull($mock->injected);
        $this->assertTrue($mock->injected->injected->publicInvoked);
        $this->assertEquals(array(), $mock->anArray);
    }

    public function testInjectPropertiesWithMultipleTypehints() {
        /** @var $mock TestMock3|Uut3 */
        $mock = $this->factory->createMock(TestMock3::CLASSNAME);
        $mock->__mock()->mockProperties(Mockster::F_ALL);

        $this->assertNotNull($mock->maybeInjected);
        $this->assertEquals(TestMock1::CLASSNAME, get_parent_class($mock->maybeInjected));

        $this->assertNull($mock->notInjected);
    }

    public function testDontInjectPropertiesWithValues() {
        /** @var $mock TestMock3|Uut3 */
        $mock = $this->factory->createMock(TestMock3::CLASSNAME, array());
        $mock->__mock()->mockProperties();

        $this->assertEquals('bar', $mock->isInitialized);
    }

    public function testInjectPropertiesWithDefaultValues() {
        /** @var $mock TestMock3|Uut3 */
        $mock = $this->factory->createMock(TestMock3::CLASSNAME);
        $mock->__mock()->mockProperties();

        $this->assertEquals('foo', $mock->hasDefaultValue);
    }

    public function testAccessPropertiesInjectedOverConstructor() {
        /** @var $mock Uut4 */
        $mock = $this->factory->createMock(TestMock4::CLASSNAME, array());
        $this->assertNotNull($mock);

        $mock->__mock()->method('invokeInjected')->dontMock();

        $this->assertNotNull($mock->injected);
        $this->assertNotNull($mock->injected->publicInvoked);
    }

    public function testReturnMockOfReturnTypeHint() {
        /** @var $mock Uut4 */
        $mock = $this->factory->createMock(TestMock4::CLASSNAME);

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
        $mock = $this->factory->createMock(TestMock4::CLASSNAME);

        $this->assertNull($mock->getTestMock3()->getTestMock2()->invokeInjected());

        $mock->__mock()->getChain('getTestMock3->getTestMock2->invokeInjected')
                ->willReturn('myValue');

        $this->assertNotNull($mock->getTestMock3());
        $this->assertEquals('myValue', $mock->getTestMock3()->getTestMock2()->invokeInjected());
    }

    public function testThrowException() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(TestMock1::CLASSNAME);

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
        $mock = $this->factory->createMock(TestMock1::CLASSNAME);

        try {
            $mock->__mock()->method('nonExistingMethod');
            $this->fail('Should have thrown an exception.');
        } catch (\InvalidArgumentException $e) {
            // should throw since method does not exist
        }
    }

    public function testWasCalledWith() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(TestMock1::CLASSNAME);

        $mock->myPublicMethod(1, 2);

        $this->assertTrue($mock->__mock()->method('myPublicMethod')->wasCalledWith(array(1, 2)));
        $this->assertTrue($mock->__mock()->method('myPublicMethod')->wasCalledWith(array('arg1' => 1, 'arg2' => 2)));
        $this->assertTrue($mock->__mock()->method('myPublicMethod')->wasCalledWith(array('arg2' => 2)));
        $this->assertTrue($mock->__mock()->method('myPublicMethod')->wasCalledWith(array('arg1' => 1)));
        $this->assertFalse($mock->__mock()->method('myPublicMethod')->wasCalledWith(array('arg3' => true)));
    }

    public function testStaticMethodIsMocked() {
        /** @var $mock StaticMock */
        $mock = $this->factory->createMock(StaticMock::CLASSNAME);

        $mock->myStaticMethod();

        $this->assertFalse(StaticMock::$called);
    }

    public function testConstructorArguments() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(TestMock1::CLASSNAME, array());

        $this->assertEquals(array(), $mock->array);
        $this->assertEquals(array(), $mock->__mock()->getConstructorArgument('array'));
        $this->assertEquals(null, $mock->mixed);
        $this->assertEquals(null, $mock->__mock()->getConstructorArgument('mixed'));
        $this->assertEquals(true, $mock->optional);
        $this->assertEquals(true, $mock->__mock()->getConstructorArgument('optional'));

        $this->assertNotNull($mock->inDoc);
        $this->assertTrue($mock->inDoc instanceof TestMock1);
        $this->assertTrue($mock->__mock()->getConstructorArgument('inDoc') instanceof TestMock1);

        $mock = $this->factory->createMock(TestMock1::CLASSNAME, array('array' => array('one')));

        $this->assertEquals(array('one'), $mock->array);
        $this->assertEquals(null, $mock->mixed);
        $this->assertEquals(true, $mock->optional);

        $mock = $this->factory->createMock(TestMock1::CLASSNAME, array(array('one'), 'two', null, false));

        $this->assertEquals(array('one'), $mock->array);
        $this->assertEquals('two', $mock->mixed);
        $this->assertEquals(false, $mock->optional);
    }

    public function testLogNotMockedMethodCalls() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(TestMock1::CLASSNAME);

        $mock->__mock()->method('myPublicMethod')->dontMock();

        $mock->myPublicMethod('hey', 'ho');

        $this->assertEquals("Method: myPublicMethod\n  called: (hey, ho) => NOT MOCKED\n",
            $mock->__mock()->method('myPublicMethod')->getHistory());
    }

    public function testMockNoMethods() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(TestMock1::CLASSNAME);

        $mock->__mock()->mockMethods(Mockster::F_NONE);

        $mock->myPublicMethod();
        $mock->myProtectedMethod();

        $this->assertEquals(true, $mock->publicInvoked);
        $this->assertEquals(true, $mock->protectedInvoked);
    }

    public function testDontMockPublicMethods() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(TestMock1::CLASSNAME);

        $mock->__mock()->mockMethods(~Mockster::F_PUBLIC);

        $mock->myPublicMethod();
        $mock->myProtectedMethod();

        $this->assertEquals(true, $mock->publicInvoked);
        $this->assertEquals(false, $mock->protectedInvoked);
    }

    public function testDontMockProtectedMethods() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(TestMock1::CLASSNAME);

        $mock->__mock()->mockMethods(~Mockster::F_PROTECTED);

        $mock->myPublicMethod();
        $mock->myProtectedMethod();

        $this->assertEquals(false, $mock->publicInvoked);
        $this->assertEquals(true, $mock->protectedInvoked);
    }

    public function testMockOnlyCertainMethods() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(TestMock1::CLASSNAME);

        $mock->__mock()->mockMethods(Mockster::F_ALL, 'mockIt');

        $mock->myPublicMethod();
        $mock->myProtectedMethod();
        $mock->myStaticMethod();
        $mock->myMockedMethod();

        $this->assertEquals(true, $mock->publicInvoked);
        $this->assertEquals(true, $mock->protectedInvoked);
        $this->assertEquals(false, $mock->mockedInvoked);
        $this->assertEquals(false, TestMock1::$staticInvoked);
    }

    public function testSingletons() {
        /** @var $mock1 Mock|Uut1 */
        $mock1 = $this->factory->createMock(TestMock1::CLASSNAME);
        $this->factory->makeSingleton(TestMock1::CLASSNAME, $mock1);

        /** @var $mock4 Uut4 */
        $mock4 = $this->factory->createMock(TestMock4::CLASSNAME);
        /** @var $mock2 Uut2 */
        $mock2 = $this->factory->createMock(TestMock2::CLASSNAME, array());

        $this->assertEquals($mock4->getTestMock(), $mock1);
        $this->assertEquals($mock4->getTestMock(), $this->factory->createMock(TestMock1::CLASSNAME));
        $this->assertEquals($mock4->getTestMock(), $mock2->injected);

    }

    public function testInheritedMethods() {
        /** @var $mock Uut1 */
        $mock = $this->factory->createMock(TestMock1::CLASSNAME);

        $mock->myPublicMethod();
        $mock->myProtectedMethod();

        $this->assertFalse($mock->publicInvoked);
        $this->assertFalse($mock->protectedInvoked);
    }

    public function testMockMethodArguments() {
        $mock = $this->factory->createMock(TestMock4::CLASSNAME);
        $mock->__mock()->method('methodWithDependencies')->dontMock();
        list($mock1, $mock2, $mock3, $array, $int) = $mock->__mock()->invoke('methodWithDependencies', array('int' => 42));

        $this->assertTrue($mock1 instanceof TestMock1);
        $this->assertTrue($mock2 instanceof TestMock2);
        $this->assertNull($mock3);
        $this->assertEquals(array(), $array);
        $this->assertEquals(42, $int);
    }

    public function testOnlyInjectAnnotatedProperties() {
        /** @var $mock Uut3 */
        $mock = $this->factory->createMock(TestMock3::CLASSNAME);
        $mock->__mock()->mockProperties(Mockster::F_PROTECTED, 'inject');

        $this->assertNotNull($mock->injected);
        $this->assertNull($mock->protectedInjected);
        $this->assertNull($mock->maybeInjected);
        $this->assertNull($mock->notInjected);
    }

    public function testInterfaceMock() {
        /** @var $mock Mock|InterfaceMock */
        $mock = $this->factory->createTestUnit(InterfaceMock::CLASSNAME);

        $mock->someMethod();
    }

    public function testAbstractMock() {
        /** @var $mock AbstractMock|Mock */
        $mock = $this->factory->createTestUnit(AbstractMock::CLASSNAME);

        $mock->abstractMethod();
        $mock->implementedMethod();

        $this->assertTrue($mock->invoked);
    }

    public function testMocksImplementMock() {
        $mock = $this->factory->createMock(TestMock1::CLASSNAME);
        $this->assertInstanceOf('\rtens\mockster\Mock', $mock);
    }

    public function testDontTryToMockMethodsCalledInConstructor() {
        /** @var $mock TestMock5 */
        $mock = $this->factory->createMock(TestMock5::CLASSNAME, array());
        $this->assertTrue($mock->wasCalled);
    }

}

/**
 * @method myProtectedMethod()
 * @method static myStaticMethod()
 * @method \rtens\mockster\Mockster __mock();
 * @property boolean staticInvoked
 */
class Uut1 extends TestMock1 {}

class TestMock1 {

    const CLASSNAME = __CLASS__;

    public $publicInvoked = false;
    public $protectedInvoked = false;
    public $mockedInvoked = false;
    public static $staticInvoked = false;

    public $array;
    public $mixed;
    public $optional;
    public $inDoc;

    public $arg1;
    public $arg2;

    /**
     * @param array $array
     * @param mixed $mixed
     * @param \spec\rtens\mockster\TestMock1 $inDoc
     * @param bool $optional
     */
    public function __construct(array $array, $mixed, $inDoc, $optional = true) {
        $this->array = $array;
        $this->mixed = $mixed;
        $this->optional = $optional;
        $this->inDoc = $inDoc;
        TestMock1::$staticInvoked = false;
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

    /**
     * @mockIt
     */
    protected static function myStaticMethod() {
        TestMock1::$staticInvoked = true;
    }

    /**
     * @mockIt
     */
    public function myMockedMethod() {
        $this->mockedInvoked = true;
    }

}

class TestMock11 extends TestMock1 {
}

/**
 * @property \rtens\mockster\Mock|TestMock1 injected
 * @method \rtens\mockster\Mockster __mock()
 * @method \object invokeInjected()
 */
class Uut2 extends TestMock2 {}

class TestMock2 {

    const CLASSNAME = __CLASS__;

    protected $injected;

    /**
     * @var \spec\rtens\mockster\TestMock1
     */
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
 * @property \rtens\mockster\Mock|Uut2 injected
 * @property \rtens\mockster\Mock|Uut2 protectedInjected
 * @property \rtens\mockster\Mock|Uut1 maybeInjected
 * @property \rtens\mockster\Mock|Uut1 notInjected
 * @property array anArray
 * @property string hasDefaultValue
 * @property string isInitialized
 * @method \rtens\mockster\Mockster __mock()
 */
class Uut3 extends TestMock3 {}

class TestMock3 {

    const CLASSNAME = __CLASS__;

    /**
     * @inject
     * @var \spec\rtens\mockster\TestMock2
     */
    protected $injected;

    /**
     * @var \spec\rtens\mockster\TestMock2
     */
    protected $protectedInjected;

    /**
     * @var \spec\rtens\mockster\TestMock1|null
     */
    public $maybeInjected;

    /**
     * @var null|\spec\rtens\mockster\TestMock1
     */
    protected $notInjected;

    /**
     * @var string[]|array
     */
    protected $anArray;

    /**
     * @var string
     */
    protected $hasDefaultValue = 'foo';

    /**
     * @var string
     */
    protected $isInitialized;

    function __construct() {
        $this->isInitialized = 'bar';
    }

    public function invokeIt() {
        $this->injected->invokeInjected();
    }

    /**
     * @return \spec\rtens\mockster\TestMock2|Uut2
     */
    public function getTestMock2() { }

}

/**
 * @property \rtens\mockster\Mock|Uut1 injected
 * @method \rtens\mockster\Mockster __mock()
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
     * @param \spec\rtens\mockster\TestMock2 $mock2
     * @param TestMock3|null $mock3
     * @return array
     */
    public function methodWithDependencies(array $array, $int, TestMock1 $mock1, $mock2, TestMock3 $mock3 = null) {
        return array($mock1, $mock2, $mock3, $array, $int);
    }

    /**
     * @return \spec\rtens\mockster\TestMock1
     */
    public function getTestMock() { }

    /**
     * @return \spec\rtens\mockster\TestMock3|Uut3
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

class TestMock5 {

    const CLASSNAME = __CLASS__;

    public $wasCalled = false;

    public function __construct() {
        $this->earlyMethod();
    }

    public function earlyMethod() {
        $this->wasCalled = true;
    }

}

class StaticMock {

    const CLASSNAME = __CLASS__;

    public static $called = false;

    public  static function myStaticMethod() {
        StaticMock::$called = true;
    }
}

interface InterfaceMock {

    const CLASSNAME = __CLASS__;

    public function someMethod();

}

abstract class AbstractMock {

    const CLASSNAME = __CLASS__;

    public $invoked = false;

    public abstract function abstractMethod();

    public function implementedMethod() {
        $this->invoked = true;
    }
}

?>
