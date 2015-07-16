<?php
namespace rtens\mockster;

use rtens\mockster\arguments\Argument;
use rtens\mockster\behaviour\Behaviour;
use rtens\mockster\behaviour\BehaviourFactory;
use watoki\factory\Factory;
use watoki\reflect\type\UnknownType;

class Stub {

    /** @var string */
    private $class;

    /** @var string */
    private $name;

    /** @var \ReflectionMethod */
    private $reflection;

    /** @var array|Argument[] */
    private $arguments;

    /** @var Behaviour */
    private $behaviour;

    /** @var ReturnTypeInferer */
    private $typeHint;

    /** @var boolean */
    private $stubbed = true;

    /** @var boolean */
    private $checkReturnType = true;

    /** @var History */
    private $history;

    /**
     * @param Factory $factory
     * @param string $class
     * @param string $name
     * @param array|Argument[] $arguments
     * @param array|History[] $collected
     * @throws \ReflectionException If the method cannot be stubbed
     */
    function __construct(Factory $factory, $class, $name, array $arguments = [], array $collected = []) {
        $this->class = $class;
        $this->name = $name;
        $this->arguments = $arguments;

        $this->reflection = new \ReflectionMethod($class, $name);
        $this->typeHint = new ReturnTypeInferer($this->reflection, $factory);
        $this->history = new History($collected);

        if ($this->reflection->isPrivate()) {
            throw new \ReflectionException("Cannot stub private methods [$this->class::$name()]");
        } else if ($this->reflection->isStatic()) {
            throw new \ReflectionException("Cannot stub static methods [$this->class::$name()]");
        }
    }

    /**
     * Sets the given Behaviour or returns a BehaviourFactory if non given
     *
     * @param behaviour\Behaviour $behaviour
     * @return BehaviourFactory|Behaviour
     */
    public function will(Behaviour $behaviour = null) {
        if ($behaviour) {
            $this->behaviour = $behaviour;
            return $behaviour;
        }
        return new BehaviourFactory(function (Behaviour $behaviour) {
            $this->setStubbed(true);
            $this->behaviour = $behaviour;
        });
    }

    /**
     * @return History
     */
    public function has() {
        return $this->history;
    }

    /**
     * @param array $arguments Indexed by position and name
     * @return mixed The return value of the first active Behaviour
     */
    public function invoke($arguments) {
        if ($this->behaviour && $this->behaviour->isActive()) {
            return $this->behaviour->invoke($this->named($arguments));
        } else {
            return $this->typeHint->mockValue();
        }
    }

    /**
     * Enables or disabled checking that the returned value matches the return type hint or
     * that a thrown Exception is declared in the doc comment.
     *
     * @param bool $enabled
     */
    public function enableReturnTypeChecking($enabled = true) {
        $this->checkReturnType = $enabled;
    }

    /**
     * Will make the method ignore stubbed behaviour and invoke the original method instead.
     */
    public function dontStub() {
        $this->setStubbed(false);
    }

    /**
     * Enable or disable stubbed behaviour.
     *
     * @param bool $stubbed
     */
    public function setStubbed($stubbed = true) {
        $this->stubbed = $stubbed;
    }

    /**
     * A not-stubbed method invokes the original method and ignores configured behaviour.
     *
     * @return bool
     */
    public function isStubbed() {
        return $this->stubbed;
    }

    /**
     * The arguments that this Stub is active for.
     *
     * @return array|arguments\Argument[]
     */
    public function arguments() {
        return $this->arguments;
    }

    /**
     * @return string
     */
    public function className() {
        return $this->class;
    }

    /**
     * @return string
     */
    public function methodName() {
        return $this->name;
    }

    /**
     * Records the invocation of the method.
     *
     * @param array $arguments
     * @param mixed $returnValue
     * @param \Exception $thrown
     * @throws \ReflectionException
     */
    public function record($arguments, $returnValue, \Exception $thrown = null) {
        $this->history->add(new Call($this->named($arguments), $returnValue, $thrown));

        if (!$this->checkReturnType) {
            return;
        }

        if ($thrown) {
            $this->checkException($thrown);
        } else {
            $this->checkReturnValue($returnValue);
        }
    }

    private function checkReturnValue($returnValue) {
        $type = $this->typeHint->getType();
        if (!$type->is($returnValue)) {
            $returned = $this->toString($returnValue);
            throw new \ReflectionException("[{$this->class}::{$this->name}()] returned [$returned] " .
                "which does not match its return type [$type]");
        }
    }

    private function checkException(\Exception $exception) {
        $type = $this->typeHint->getExceptionType();

        if ($type instanceof UnknownType || !$type->is($exception)) {
            throw new \ReflectionException("[{$this->class}::{$this->name}()] threw "
                . get_class($exception) . '(' . $exception->getMessage() . ') '
                . "without proper annotation");
        }
    }

    private function toString($value) {
        if (is_object($value)) {
            return get_class($value);
        } else if (is_array($value)) {
            return 'array';
        } else {
            return print_r($value, true);
        }
    }

    private function named($arguments) {
        foreach ($this->reflection->getParameters() as $param) {
            if (array_key_exists($param->getPosition(), $arguments)) {
                $arguments[$param->getName()] = $arguments[$param->getPosition()];
            } elseif ($param->isDefaultValueAvailable()) {
                $arguments[$param->getPosition()] = $param->getDefaultValue();
                $arguments[$param->getName()] = $param->getDefaultValue();
            }
        }
        return $arguments;
    }
}