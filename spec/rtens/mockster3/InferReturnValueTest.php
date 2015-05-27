<?php
namespace spec\rtens\mockster3;

use rtens\mockster3\Mockster;
use watoki\scrut\Specification;

class InferReturnValueTest extends Specification {

    private $foo;

    /** @var InferReturnValue_FooClass $mock */
    private $mock;

    protected function setUp() {
        parent::setUp();
        $this->foo = new Mockster(InferReturnValue_FooClass::$class);
        $this->mock = $this->foo->mock();
    }

    function testInferPrimitive() {
        $this->assertEquals(0, $this->mock->returnInt());
        $this->assertEquals(0.0, $this->mock->returnFloat());
        $this->assertEquals(false, $this->mock->returnBoolean());
        $this->assertEquals("", $this->mock->returnString());
        $this->assertEquals([], $this->mock->returnArray());
        $this->assertEquals(null, $this->mock->returnNull());
        $this->assertEquals("", $this->mock->returnMulti());
        $this->assertEquals(null, $this->mock->returnNullableObject());
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertEquals(null, $this->mock->returnVoid());
    }

    function testInferClass() {
        $this->assertInstanceOf(get_class($this->foo), $this->mock->fullClassName());
        $this->assertInstanceOf(get_class($this->foo), $this->mock->importedClass());
    }

    function testRecursiveFaking() {
        $this->assertInstanceOf(InferReturnValue_FooClass::$class, $this->mock->recursive()->recursive()->recursive());
    }
}

class InferReturnValue_FooClass {
    public static $class = __CLASS__;

    /**
     * @return int
     */
    public function returnInt() {
        return 42;
    }

    /**
     * @return bool
     */
    public function returnBoolean() {
        return true;
    }

    /**
     * @return float
     */
    public function returnFloat() {
        return 42.0;
    }

    /**
     * @return string
     */
    public function returnString() {
        return "foo";
    }

    /**
     * @return array
     */
    public function returnArray() {
        return array("foo");
    }

    /**
     * @return null
     */
    public function returnNull() {
        return "foo";
    }

    /**
     * @return void
     */
    public function returnVoid() {
        return null;
    }

    /**
     * @return null|\DateTime
     */
    public function returnNullableObject() {
        return new \DateTime();
    }

    /**
     * @return string|int
     */
    public function returnMulti() {
        return "foo";
    }

    /**
     * @return \rtens\mockster3\Mockster
     */
    public function fullClassName() {
        return null;
    }

    /**
     * @return Mockster
     */
    public function importedClass() {
        return null;
    }

    /**
     * @return InferReturnValue_FooClass
     */
    public function recursive() {
        return null;
    }
}