<?php
namespace rtens\mockster;

class History {

    /** @var bool */
    private static $enabled = true;

    /** @var \ReflectionMethod */
    private $reflection;

    /** @var array|array[] Collection of called arguments (named) */
    private $calledArguments = array();

    /** @var array|mixed[] Collection of returned values */
    private $returnedValues = array();

    function __construct(\ReflectionMethod $reflection) {
        $this->reflection = $reflection;
    }

    public static function enable() {
        self::$enabled = true;
    }

    public static function disable() {
        self::$enabled = false;
    }

    /**
     * @return bool
     */
    public function wasCalled() {
        return $this->getCalledCount() > 0;
    }

    /**
     * @param array $arguments
     * @param mixed $returnValue
     * @return void
     */
    public function log($arguments, $returnValue) {
        if (!self::$enabled) {
            return;
        }

        $parameters = array();
        foreach ($this->reflection->getParameters() as $param) {
            if (array_key_exists($param->getPosition(), $arguments)) {
                $parameters[$param->getName()] = $arguments[$param->getPosition()];
            }
        }

        $this->calledArguments[] = $parameters;
        $this->returnedValues[] = $returnValue;
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
                    if (!isset($args[$name]) || $args[$name] != $value) {
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

    public function __toString() {
        return $this->toString();
    }

    public function toString($verbosity = 0) {
        $history = $this->reflection->getName() . ' (' . $this->getCalledCount() . ')' . PHP_EOL;
        $returned = $this->getReturnedValues();
        foreach ($this->getCalledArguments() as $i => $args) {
            $argsStrings = array();
            foreach ($args as $arg) {
                $argsStrings[] = $this->asString($arg, $verbosity);
            }
            $history .= '  (' . implode(', ', $argsStrings) . ') -> '
                . $this->asString($returned[$i], $verbosity) . "\n";
        }

        return $history;
    }

    private function asString($arg, $verbosity) {
        if (is_object($arg)) {
            if ($verbosity == 0) {
                $classname = explode('\\', get_class($arg));
                return end($classname);
            }
            return get_class($arg);

        } else if (is_array($arg)) {
            if ($verbosity == 0 || count($arg) == 0) {
                return 'array(' . count($arg) . ')';
            }

            $pairs = array();
            $keys = array_keys($arg);
            $isList = $keys[0] === 0;
            foreach ($arg as $key => $value) {
                $pairs[] = ($isList ? '' : (print_r($key, true) . ' => ')) . $this->asString($value, $verbosity - 1);
            }
            return '[' . implode(', ', $pairs) . ']';
        } else {
            return print_r($arg, true);
        }
    }

}
