<?php
namespace rtens\mockster3;

use watoki\factory\Factory;

class Mockster {

    /** @var string */
    private $class;

    /** @var \rtens\mockster3\Stubs */
    private $stubs;

    /** @var \watoki\factory\Factory */
    private $factory;

    /**
     * @param string $class The FQN of the class to mock
     */
    function __construct($class) {
        $this->class = $class;
        $this->stubs = new Stubs($class);
        $this->factory = new Factory();
        $this->factory->setProvider('StdClass', new MockProvider($this->factory));
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
        return $this->injectStubs($this->factory->getInstance($this->class, null));
    }

    public function uut($constructorArguments = []) {
        $this->stubs->stubbedByDefault(false);
        return $this->injectStubs($this->factory->getInstance($this->class, $constructorArguments));
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

    private function injectStubs($instance) {
        $instance->__stubs = $this->stubs;
        return $instance;
    }


}