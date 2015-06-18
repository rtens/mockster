<?php
namespace rtens\mockster3;

use watoki\collections\Map;
use watoki\factory\Factory;
use watoki\reflect\Property;
use watoki\reflect\PropertyReader;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\MultiType;
use watoki\reflect\type\NullableType;

class Mockster {

    public static $enableReturnTypeChecking = true;

    /** @var string */
    private $class;

    /** @var \rtens\mockster3\Stubs */
    private $stubs;

    /** @var \watoki\factory\Factory */
    private $factory;

    /** @var Map|Property[] */
    private $properties;

    /** @var Mockster[] */
    private $propertyMocksters = [];

    /** @var array|object[] */
    private $uuts = [];

    /**
     * @param string $class The FQN of the class to mock
     * @param null|Factory $factory
     */
    function __construct($class, Factory $factory = null) {
        if (!$factory) {
            $factory = new Factory();
            $factory->setProvider('StdClass', new MockProvider($factory));
        }
        $this->class = $class;
        $this->factory = $factory;
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

            if ($this->uuts) {
                $mockster = $this->properties[$name]->get($this->uuts[0])->__mockster;
            } else {
                $mockster = new Mockster($this->getTypeHint($name), $this->factory);
            }

            $this->propertyMocksters[$name] = $mockster;
        }
        return $this->propertyMocksters[$name];
    }

    /**
     * @return object A mocked instance of the class, with all methods stubbed and created without invoking
     * the parent constructor
     */
    public function mock() {
        return $this->prepMock($this->factory->getInstance($this->class, MockProvider::NO_CONSTRUCTOR));
    }

    /**
     * @param array $constructorArguments
     * @return object The Unit Under Test - an instance of the class, methods are not stubbed and the parent
     * constructor is called, mocks of dependencies are injected
     */
    public function uut($constructorArguments = []) {
        $this->stubs->stubbedByDefault(false);
        $instance = $this->prepMock($this->factory->getInstance($this->class, $constructorArguments));

        $this->uuts[] = $instance;
        foreach ($this->propertyMocksters as $property => $mockster) {
            $this->properties[$property]->set($instance, $mockster->mock());
        }

        return $instance;
    }

    private function prepMock($instance) {
        $instance->__stubs = $this->stubs;
        $instance->__mockster = $this;
        return $instance;
    }

    private function getTypeHint($propertyName) {
        $type = $this->properties[$propertyName]->type();

        if ($type instanceof NullableType) {
            $type = $type->getType();
        }

        if ($type instanceof ClassType) {
            return $type->getClass();
        } else if ($type instanceof MultiType) {
            foreach ($type->getTypes() as $aType) {
                if ($aType instanceof ClassType) {
                    return $aType->getClass();
                }
            }
        }

        throw new \ReflectionException("Property [{$this->class}::$propertyName] " .
            "cannot be mocked since it's type hint is not a class.");
    }
}