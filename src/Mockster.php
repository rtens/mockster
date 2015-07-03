<?php
namespace rtens\mockster;

use watoki\collections\Map;
use watoki\factory\Factory;
use watoki\reflect\Property;
use watoki\reflect\PropertyReader;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\MultiType;
use watoki\reflect\type\NullableType;

class Mockster {

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

    /** @var array|object[] */
    private $uuts = [];

    /**
     * @param string $class The FQN of the class to mock
     * @param null|Factory $factory
     */
    function __construct($class, Factory $factory = null) {
        $this->class = $class;
        $this->factory = $factory ?: self::createFactory();
        $this->stubs = new Stubs($class, $this->factory);
        $this->properties = (new PropertyReader($this->class))->readState();
    }

    /**
     * Creates a Factory with MockProvider set as default Provider
     *
     * @param callable $configureMockProvider Receives the MockProvider to be configured
     * @return Factory
     */
    public static function createFactory(callable $configureMockProvider = null) {
        $factory = new Factory();

        $provider = new MockProvider($factory);

        if ($configureMockProvider) {
            $configureMockProvider($provider);
        }

        $factory->setProvider('StdClass', $provider);
        return $factory;
    }

    /**
     * @param string $class
     * @param Factory $factory
     * @return Mockster
     */
    public static function of($class, Factory $factory = null) {
        return new Mockster($class, $factory);
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
     * @param Mockster|string|object $mockster
     * @return object
     */
    public static function mock($mockster) {
        if (!($mockster instanceof Mockster)) {
            $mockster = Mockster::of($mockster);
        }
        return $mockster->__mock();
    }

    /**
     * @param Mockster|string|object $mockster
     * @param array $constructorArguments
     * @return object
     */
    public static function uut($mockster, array $constructorArguments = []) {
        if (!($mockster instanceof Mockster)) {
            $mockster = Mockster::of($mockster);
        }
        return $mockster->__uut($constructorArguments);
    }

    /**
     * Enables or disabled for all stubbed methods checking that the returned value matches the return type hint or
     * that a thrown Exception is declared in the doc comment.
     *
     * @param bool $enabled
     */
    public function enableReturnTypeChecking($enabled = true) {
        $this->stubs->enableReturnTypeChecking($enabled);
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
    public function __mock() {
        return $this->prepMock($this->factory->getInstance($this->class, MockProvider::NO_CONSTRUCTOR));
    }

    /**
     * @param array $constructorArguments
     * @return object The Unit Under Test - an instance of the class, methods are not stubbed and the parent
     * constructor is called, mocks of dependencies are injected
     */
    public function __uut($constructorArguments = []) {
        $this->stubs->stubbedByDefault(false);
        $instance = $this->prepMock($this->factory->getInstance($this->class, $constructorArguments));

        $this->uuts[] = $instance;
        foreach ($this->propertyMocksters as $property => $mockster) {
            $this->properties[$property]->set($instance, $mockster->__mock());
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