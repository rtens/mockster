<?php
namespace rtens\mockster;

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

    /** @var array|Method[] */
    private $stubs = array();

    /** @var string Name of this class */
    private $classname;

    /** @var array|\ReflectionMethod[] */
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

        $reflection = new \ReflectionClass($classname);
        $this->methods = $reflection->getMethods();
    }

    /**
     * @return string History of method calls on this mock
     */
    public function getHistory() {
        $history = '';
        foreach ($this->stubs as $stub) {
            $history .= "\n" . $stub->getHistory();
        }

        return $history;
    }

    /**
     * @param int $filter Using bit-combinations of Mockster::F_* (e.g. Mockster::F_PUBLIC | Mockster::F_PROTECTED)
     * @param null|callable $customFilter
     * @throws \Exception If a property cannot be mocked because the class of the type hint cannot be found
     */
    public function mockProperties($filter = Mockster::F_PROTECTED, $customFilter = null) {
        $callback = $this->getFilterCallback($filter, $customFilter);
        $this->injector->injectProperties($this->mock,
            function (\ReflectionProperty $property) use ($callback) {
                return !$property->isPrivate() && $callback($property);
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
        $filter = $this->getFilterCallback($filter, $customFilter);
        foreach ($this->methods as $method) {
            $this->method($method->getName())->setMocked($filter($method));
        }
    }

    private function getFilterCallback($filter, $customFilter) {
        return function ($member) use ($filter, $customFilter) {
            /** @var \ReflectionProperty $member */
            return
                (!$member->isPublic() || ($filter & self::F_PUBLIC) == self::F_PUBLIC) &&
                (!$member->isProtected() || ($filter & self::F_PROTECTED) == self::F_PROTECTED) &&
                (!$member->isStatic() || ($filter & self::F_STATIC) == self::F_STATIC) &&
                (!$customFilter || $customFilter($member));
        };
    }

    /**
     * @param string $methodName
     * @throws \InvalidArgumentException
     * @return Method
     */
    public function method($methodName) {
        foreach ($this->methods as $method) {
            if ($method->getName() == $methodName) {

                if (!array_key_exists($methodName, $this->stubs)) {
                    $this->stubs[$methodName] = new Method($this->factory, $method);
                }

                return $this->stubs[$methodName];
            }
        }

        throw new \InvalidArgumentException(sprintf("Can't mock method '%s'. Does not exist in class '%s'",
            $methodName, $this->classname));
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

}

?>
