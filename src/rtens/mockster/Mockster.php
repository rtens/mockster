<?php
namespace rtens\mockster;

use watoki\factory\Factory;

class Mockster {

    /** @var string */
    private $class;

    /** @var \rtens\mockster\Stubs */
    private $stubs;

    /**
     * @param string $class The FQN of the class to mock
     */
    function __construct($class) {
        $this->class = $class;
        $this->stubs = new Stubs($class);
        $this->factory = new Factory();
        $this->factory->setProvider('StdClass', new MockProvider());
    }

    /**
     * The sole purpose of this method is its type hint
     * @param Stub|mixed $stub
     * @return Stub
     */
    public static function stub(Stub $stub) {
        return $stub;
    }

    /**
     * @return object A mock-instance of the class
     */
    public function mock() {
        $mock = $this->factory->getInstance($this->class);
        $mock->__stubs = $this->stubs;
        return $mock;
    }

    /**
     * Intercepts all method call and returns a corresponding MethodCall object
     *
     * @param string $name
     * @param array $arguments
     * @return Stub
     */
    function __call($name, $arguments) {
        return $this->stubs->add($name, $arguments);
    }


}