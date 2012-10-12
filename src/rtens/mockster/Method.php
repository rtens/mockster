<?php
namespace rtens\mockster;
use rtens\mockster\behaviour\ReturnValueBehaviour;
use rtens\mockster\behaviour\CallbackBehaviour;
use rtens\mockster\behaviour\ThrowExceptionBehaviour;

/**
 * A mocked method collects all its invokation all forwards them to a Behaviour if set.
 *
 * Arguments are saved with their parameter names but can also be accessed via their position.
 */
class Method {

    /**
     * @var \ReflectionMethod
     */
    private $reflection;

    /**
     * @var array|array[] Collection of called arguments (named)
     */
    private $calledArguments = array();

    /**
     * @var array|mixed[] Collection of returned values
     */
    private $returnedValues = array();

    /**
     * @var array|Behaviour[] Registered behaviours
     */
    private $behaviours = array();

    /**
     * @var boolean
     */
    private $mocked = true;

    /**
     * @var Generator
     */
    private $generator;

    /**
     * @param \rtens\mockster\MockFactory $factory
     * @param \ReflectionMethod $reflection
     */
    public function __construct(MockFactory $factory, \ReflectionMethod $reflection) {
        $this->reflection = $reflection;
        $this->generator = new Generator($factory);
    }

    /**
     * Called when the method is invoked.
     *
     * @param array $arguments List of arguments
     * @return mixed The return value
     */
    public function invoke($arguments) {

        $this->log($arguments);

        foreach ($this->behaviours as $behaviour) {
            if ($behaviour->appliesTo($arguments)) {
                $value = $behaviour->getReturnValue($arguments);
                $this->returnedValues[] = $value;
                return $value;
            }
        }

        $value = $this->getReturnTypeHintMock();
        $this->returnedValues[] = $value;
        return $value;
    }

    /**
     * @param array $arguments
     * @return void
     */
    public function log($arguments) {
        $parameters = array();
        foreach ($this->reflection->getParameters() as $i => $param) {
            $value = null;
            if (array_key_exists($i, $arguments)) {
                $value = $arguments[$i];
            }
            if ($value) {
                $parameters[$param->getName()] = $value;
            }
        }

        $this->calledArguments[] = $parameters;
        $this->returnedValues[] = 'NOT MOCKED';
    }

    /**
     * @throws \Exception
     * @return array|bool|float|int|Mock|null|string
     */
    public function getReturnTypeHintMock() {
        $matches = array();
        if (preg_match('/@return (\S*)/', $this->reflection->getDocComment(), $matches)) {
            try {
                return $this->generator->getInstanceFromHint($matches[1]);
            } catch (\InvalidArgumentException $e) {
                throw new \Exception("Error while creating mock from return type hint of " .
                        $this->reflection->getDeclaringClass()->getShortName() . '::' . $this->reflection->getName() .
                        ': ' . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * @param bool $mocked
     * @return Method
     */
    public function setMocked($mocked = true) {
        $this->mocked = $mocked;
        return $this;
    }

    /**
     * Sets the method to not-mocked
     */
    public function dontMock() {
        $this->setMocked(false);
    }

    /**
     * @return bool
     */
    public function isMocked() {
        return $this->mocked;
    }

    /**
     * @return string Name of the mocked method
     */
    public function getName() {
        return $this->reflection->getName();
    }

    /**
     * @return string History with called arguments and returned value
     */
    public function getHistory() {
        $history = 'Method: ' . $this->getName() . "\n";
        $returned = $this->getReturnedValues();
        foreach ($this->getCalledArguments() as $i => $args) {
            $argsStrings = array();
            foreach ($args as $arg) {
                if (is_object($arg)) {
                    $classname = explode('\\', get_class($arg));
                    $argsStrings[] = end($classname);
                } else if (is_array($arg)) {
                    $argsStrings[] = 'array(' . implode(', ', array_keys($arg)) . ')';
                } else {
                    $argsStrings[] = print_r($arg, true);
                }
            }
            $history .= '  called: (' . implode(', ', $argsStrings) . ') => ' . print_r($returned[$i], true) . "\n";
        }

        return $history;
    }

    /**
     * @return array Off all arguments this method was invoked with indexed by their parameter names.
     */
    public function getCalledArguments() {
        return $this->calledArguments;
    }

    /**
     * @param int $index The arguments of the first invokation will be at index 0. If negative, counts from end.
     * @return array Of one set of arguments indexed by their parameter names.
     */
    public function getCalledArgumentsAt($index) {
        if ($index < 0) {
            $index = count($this->calledArguments) + $index;
        }

        return $this->calledArguments[$index];
    }

    /**
     * @param int $index Index of the invokation (see getCalledArgumentsAt)
     * @param int|string $paramNameOrIndex Number for position or parameter name
     * @return mixed Argument at position or with name $paramIndex of the $index'th call
     */
    public function getCalledArgumentAt($index, $paramNameOrIndex) {
        $args = $this->getCalledArgumentsAt($index);
        if (is_numeric($paramNameOrIndex)) {
            $args = array_values($args);
        }
        return $args[$paramNameOrIndex];
    }

    /**
     * @return int Number of times the method was invoked
     */
    public function getCalledCount() {
        return count($this->calledArguments);
    }

    public function getReturnedValues() {
        return $this->returnedValues;
    }

    /**
     * The arguments can be either given as a list (numeric indices) or as a map with the parameter names as indices.
     *
     * A list is compared for exact matching with the invoked arguments. A map can be incomplete and out of order.
     *
     * @param array $arguments Either a list or a map of the argument.
     * @return bool If any invokation of the method matches the given arguments.
     */
    public function wasCalledWith($arguments) {
        $keys = array_keys($arguments);

        foreach ($this->calledArguments as $args) {
            if (!empty($arguments) && is_numeric($keys[0])) {
                if (array_values($args) == $arguments) {
                    return true;
                }
            } else {
                $allMatch = true;
                foreach ($arguments as $name => $value) {
                    if ($args[$name] != $value) {
                        $allMatch = false;
                        break;
                    }
                }

                if ($allMatch) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Will forward an invokation to given Behaviour if it applies.
     *
     * @param Behaviour $doThis
     * @return Behaviour
     */
    public function willDo(Behaviour $doThis) {
        array_unshift($this->behaviours, $doThis);
        $this->setMocked();
        return $doThis;
    }

    /**
     * @param mixed $value
     * @return \rtens\mockster\behaviour\ReturnValueBehaviour
     */
    public function willReturn($value) {
        return $this->willDo(new ReturnValueBehaviour($value));
    }

    /**
     * @param \Exception $exception
     * @return \rtens\mockster\behaviour\ThrowExceptionBehaviour
     */
    public function willThrow($exception) {
        return $this->willDo(new ThrowExceptionBehaviour($exception));
    }

    /**
     * @param \callable $callback
     * @return \rtens\mockster\behaviour\CallBackBehaviour
     */
    public function willCall($callback) {
        return $this->willDo(new CallbackBehaviour($callback));
    }

    /**
     * @return bool
     */
    public function wasCalled() {
        return $this->getCalledCount() > 0;
    }

}
?>