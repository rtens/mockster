<?php
namespace spec\rtens\mockster3;

use rtens\mockster3\Mockster;
use watoki\scrut\tests\statics\StaticTestSuite;

class InferReturnValueSpec extends StaticTestSuite {

    private $foo;

    /** @var InferReturnValue_FooClass $mock */
    private $mock;

    protected function before() {
        parent::before();
        $this->foo = new Mockster(InferReturnValue_FooClass::class);
        $this->mock = $this->foo->mock();
    }

    function testInferPrimitive() {
        $this->assert($this->mock->returnInt(), 0);
        $this->assert($this->mock->returnFloat(), 0.0);
        $this->assert($this->mock->returnBoolean(), false);
        $this->assert($this->mock->returnString(), "");
        $this->assert($this->mock->returnArray(), []);
        $this->assert($this->mock->returnNull(), null);
        $this->assert($this->mock->returnMulti(), "");
        $this->assert($this->mock->returnNullableObject(), null);
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assert($this->mock->returnVoid(), null);
    }

    function testInferClass() {
        $this->assert->isInstanceOf($this->mock->fullClassName(), Mockster::class);
        $this->assert->isInstanceOf($this->mock->importedClass(), Mockster::class);
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
}