<?php
namespace spec\rtens\mockster3;

use rtens\mockster3\Mockster;
use watoki\scrut\Specification;

class InferReturnValueTest extends Specification {

    function testInferPrimitive() {
        $foo = new Mockster(InferReturnValue_FooClass::class);
        /** @var InferReturnValue_FooClass $mock */
        $mock = $foo->mock();
        $this->assertEquals(0, $mock->returnInt());
        $this->assertEquals(0.0, $mock->returnFloat());
        $this->assertEquals(false, $mock->returnBoolean());
        $this->assertEquals("", $mock->returnString());
        $this->assertEquals([], $mock->returnArray());
        $this->assertEquals(null, $mock->returnNull());
        $this->assertEquals("", $mock->returnMulti());
    }
}

class InferReturnValue_FooClass {

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
     * @return string|int
     */
    public function returnMulti() {
        return "foo";
    }
}