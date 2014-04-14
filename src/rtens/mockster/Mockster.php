<?php
namespace rtens\mockster;

use rtens\mockster\filter\Filter;
use watoki\factory\Injector;

class Mockster {

    const F_NONE = 0;

    const F_PUBLIC = 1;

    const F_PROTECTED = 2;

    const F_STATIC = 4;

    const F_NON_STATIC = 3;

    const F_ALL = 7;

    /** @var string Code of the generated Mock class */
    private $code;

    /** @var string Name of this class */
    private $classname;

    /** @var MethodCollection */
    private $methods;

    /** @var \rtens\mockster\Mock Back-reference to parent */
    private $mock;

    /** @var MockFactory */
    private $factory;

    /** @var Injector */
    private $injector;

    /** @var array|array[] Arguments of the constructor indexed by parameter name */
    private $constructorArguments = array();

    public function __construct(MockFactory $factory, $classname, $mock, $constructorArguments, $code) {
        $this->factory = $factory;
        $this->classname = $classname;
        $this->mock = $mock;
        $this->constructorArguments = $constructorArguments;
        $this->code = $code;
        $this->injector = new Injector($factory);
        $this->injector->setThrowWhenCantInjectProperty(false);

        $reflection = new \ReflectionClass($classname);
        $this->methods = new MethodCollection($factory, $classname, $reflection->getMethods());
    }

    /**
     * @param int $verbosity
     * @return string History of method calls on this mock
     */
    public function getHistory($verbosity = 0) {
        return $this->methods->getHistory($verbosity);
    }

    /**
     * @param int $filter Using bit-combinations of Mockster::F_* (e.g. Mockster::F_PUBLIC | Mockster::F_PROTECTED)
     * @param null|callable $customFilter
     * @throws \Exception If a property cannot be mocked because the class of the type hint cannot be found
     */
    public function mockProperties($filter = Mockster::F_ALL, $customFilter = null) {
        $filter = new Filter($filter, $customFilter);
        $this->injector->injectProperties($this->mock,
            function (\ReflectionProperty $property) use ($filter) {
                return $filter->apply($property);
            },
            new \ReflectionClass($this->classname));
    }

    /**
     * Sets all methods matching the filter to being mocked, and all others to not being mocked.
     *
     * @param $filter int Constants from Mockster::F_
     * @param null|callable $customFilter
     */
    public function mockMethods($filter = Mockster::F_ALL, $customFilter = null) {
        $this->methods()->dontMock()->filter($filter, $customFilter)->setMocked(true);
    }

    /**
     * @return MethodCollection
     */
    public function methods() {
        return $this->methods;
    }

    /**
     * @param string $methodName
     * @throws \InvalidArgumentException
     * @return Method
     */
    public function method($methodName) {
        return $this->methods()->method($methodName);
    }

    /**
     * @param string $propertyName
     * @return Mock
     */
    public function get($propertyName) {
        $property = new \ReflectionProperty($this->classname, $propertyName);
        $property->setAccessible(true);
        return $property->getValue($this->mock);
    }

    /**
     * @param string $propertyName
     * @param mixed $value
     */
    public function set($propertyName, $value) {
        $property = new \ReflectionProperty($this->classname, $propertyName);
        $property->setAccessible(true);
        $property->setValue($this->mock, $value);
    }

    /**
     * Calls the given method with the given arguments.
     *
     * All missing arguments will be replaced by mocks if possible or null.
     *
     * @param string $methodName
     * @param array $arguments Indexed by positions or parameter names
     * @return mixed The return value of the method
     */
    public function invoke($methodName, $arguments = array()) {
        $method = new \ReflectionMethod($this->classname, $methodName);
        $arguments = $this->injector->injectMethodArguments($method, $arguments);
        return call_user_func_array(array($this->mock, $methodName), array_values($arguments));
    }

    /**
     * @param string|int $parameterNameOrIndex
     * @throws \InvalidArgumentException If the parameter does not exist
     * @return array
     */
    public function getConstructorArgument($parameterNameOrIndex) {
        $arguments = $this->constructorArguments;
        if (is_integer($parameterNameOrIndex)) {
            $arguments = array_values($arguments);
        }
        if (!array_key_exists($parameterNameOrIndex, $arguments)) {
            throw new \InvalidArgumentException('The constructor does not have an parameter [' . $parameterNameOrIndex . ']');
        }

        return $arguments[$parameterNameOrIndex];
    }

    /**
     * Returns the stub of several sequential method calls like
     *  $this->getA()->getB()->foo();
     *
     * @param string $methodNames Method names separated by arrows (e.g. 'getA->getB->foo')
     * @throws \Exception
     * @return Method
     */
    public function getChain($methodNames) {
        /** @var $mock Mock */
        $mock = $this->mock;

        $chain = explode('->', $methodNames);
        $lastMethod = array_pop($chain);

        foreach ($chain as $methodName) {
            $stub = $mock->__mock()->method($methodName);
            $next = $stub->getReturnTypeHintMock();

            if (!$next) {
                throw new \Exception('Could not resolve type hint of [' . $methodName . '] in chain [' . $methodNames . '].');
            }

            $stub->willReturn($next);

            $mock = $next;
        }

        return $mock->__mock()->method($lastMethod);
    }

    /**
     * returns the code of the mock class with line numbers
     *
     * @return string
     */
    public function getCode() {
        $code = '';
        foreach (explode("\n", $this->code) as $i => $line) {
            $code .= ($i + 1) . ': ' . $line . "\n";
        }
        return $code;
    }

    /**
     * Un-mocks all methods and injects mocks for all properties passing the filter.
     *
     * @param callable $propertyFilter will be passed a \ReflectionProperty instance
     * @return \rtens\mockster\Mock
     */
    public function makeTestUnit($propertyFilter = null) {
        $this->mockMethods(Mockster::F_NONE);
        $this->mockProperties(Mockster::F_ALL, $propertyFilter);
        return $this->mock;
    }

    /**
     * @return string
     */
    public function getClassname() {
        return $this->classname;
    }

}

?>
