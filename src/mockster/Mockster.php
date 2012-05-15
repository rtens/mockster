<?php
namespace mockster;

class Mockster {

    /**
     * @var string Code of the generated Mock class
     */
    private $code;

    /**
     * @var array|Method[]
     */
    private $stubs = array();

    /**
     * @var string Name of this class
     */
    private $classname;

    /**
     * @var array|\ReflectionMethod[]
     */
    private $methods;

    /**
     * @var \mockster\Mock Back-reference to parent
     */
    private $mock;

    /**
     * @var MockFactory
     */
    private $factory;

    /**
     * @var Generator
     */
    private $generator;

    /**
     * @var array|array[] Arguments of the constructor indexed by parameter name
     */
    private $constructorArguments = array();

    public function __construct(MockFactory $factory, $classname, $mock, $constructorArguments, $code = null) {
        $this->factory = $factory;
        $this->classname = $classname;
        $this->mock = $mock;
        $this->constructorArguments = $constructorArguments;
        $this->code = $code;
        $this->generator = new Generator($factory);

        $refl = new \ReflectionClass($classname);
        $this->methods = $refl->getMethods();
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
     * Calls dontMock() on all methods
     */
    public function dontMockAllMethods() {
        foreach ($this->methods as $method) {
            $this->method($method->getName())->dontMock();
        }
    }

    /**
     * Calls dontMock() on all protected methods
     */
    public function dontMockProtectedMethods() {
        foreach ($this->methods as $method) {
            if ($method->isProtected()) {
                $this->method($method->getName())->dontMock();
            }
        }
    }

    /**
     * Calls dontMock() on all public methods
     */
    public function dontMockPublicMethods() {
        foreach ($this->methods as $method) {
            if ($method->isPublic()) {
                $this->method($method->getName())->dontMock();
            }
        }
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
        $arguments = $this->generator->getMethodParameters($method, $arguments);
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
     * @return string
     */
    public function getCode() {
        $code = '';
        foreach (explode("\n", $this->code) as $i => $line) {
            $code .= ($i+1) . ': ' . $line . "\n";
        }
        return $code;
    }

}
?>
