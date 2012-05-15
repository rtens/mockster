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
     * @var array|array[] Arguments of the constructor indexed by parameter name
     */
    private $constructorArguments = array();

    public function __construct($classname, $mock) {
        $this->classname = $classname;
        $this->mock = $mock;

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
                    $this->stubs[$methodName] = new Method($method);
                }

                return $this->stubs[$methodName];
            }
        }

        throw new \InvalidArgumentException(sprintf("Can't mock method '%s'. Does not exist in class '%s'",
                        $methodName, $this->classname));
    }

    /**
     * @param $parameterName
     * @throws \InvalidArgumentException If the parameter does not exist
     * @param string $parameterName
     * @return array
     */
    public function getConstructorArgument($parameterName) {
        if (!array_key_exists($parameterName, $this->constructorArguments)) {
            throw new \InvalidArgumentException('The constructor does not have an parameter [' . $parameterName . ']');
        }

        return $this->constructorArguments[$parameterName];
    }

    /**
     * @param array|array $arguments
     */
    public function setConstructorArguments($arguments) {
        $this->constructorArguments = $arguments;
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
     * @param string $code
     */
    public function setCode($code) {
        $this->code = $code;
    }

    /**
     * Prints the code of the mock class with line numbers
     */
    public function printCode() {
        foreach (explode("\n", $this->code) as $i => $line) {
            echo ($i+1) . ': ' . $line . "\n";
        }
    }

}
?>
