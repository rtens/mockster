<?php
namespace rtens\mockster;

use watoki\collections\Map;
use watoki\factory\Factory;
use watoki\reflect\Property;
use watoki\reflect\PropertyReader;
use watoki\reflect\type\ClassType;

class Mockster {

    public static $enableReturnTypeChecking = true;

    /** @var string */
    private $class;

    /** @var \rtens\mockster\Stubs */
    private $stubs;

    /** @var \watoki\factory\Factory */
    private $factory;

    /** @var Map|Property[] */
    private $properties;

    /** @var Mockster[] */
    private $propertyMocksters = [];

    /**
     * @param string $class The FQN of the class to mock
     * @param null|Factory $factory
     */
    function __construct($class, Factory $factory = null) {
        $this->class = $class;
        $this->factory = $factory ?: new Factory();
        $this->factory->setProvider('StdClass', new MockProvider($this->factory));
        $this->stubs = new Stubs($class, $this->factory);
        $this->properties = (new PropertyReader($this->class))->readState();
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
     * Intercepts all method call and returns a corresponding MethodCall object
     *
     * @param string $name
     * @param array $arguments
     * @return Stub
     */
    public function __call($name, $arguments) {
        return $this->stubs->add($name, $arguments);
    }

    /**
     * Intercepts all reading property accesses and return corresponding Mockster instance
     *
     * @param string $name
     * @return Mockster
     * @throws \ReflectionException
     */
    public function __get($name) {
        if (!array_key_exists($name, $this->propertyMocksters)) {
            if (!$this->properties->has($name)) {
                throw new \ReflectionException("The property [$this->class::$name] does not exist");
            }
            if (!$this->isMockable($this->properties[$name])) {
                throw new \ReflectionException("Property [$name] cannot be mocked since it's type hint is not a class");
            }

            /** @var ClassType $type */
            $type = $this->properties[$name]->type();
            $this->propertyMocksters[$name] = new Mockster($type->getClass(), $this->factory);
        }
        return $this->propertyMocksters[$name];
    }

    /**
     * @return object A mocked instance of the class, with all methods stubbed and created without invoking
     * the parent constructor
     */
    public function mock() {
        return $this->injectStubs($this->factory->getInstance($this->class, MockProvider::NO_CONSTRUCTOR));
    }

    /**
     * @param array $constructorArguments
     * @return object The Unit Under Test - an instance of the class, methods are not stubbed and the parent
     * constructor is called, mocks of dependencies are injected
     */
    public function uut($constructorArguments = []) {
        $this->stubs->stubbedByDefault(false);
        $instance = $this->injectStubs($this->factory->getInstance($this->class, $constructorArguments));
        $this->injectProperties($instance);
        return $instance;
    }

    private function injectStubs($instance) {
        $instance->__stubs = $this->stubs;
        return $instance;
    }

    private function injectProperties($instance) {
        foreach ($this->properties as $property) {
            if ($this->isMockable($property)) {
                $property->set($instance, $this->__get($property->name())->mock());
            }
        }
    }

    private function isMockable(Property $property) {
        return $property->type() instanceof ClassType;
    }
}