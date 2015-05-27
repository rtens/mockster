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

        $this->checkReturnType = Mockster::$enableReturnTypeChecking;

        $this->reflection = new \ReflectionMethod($class, $name);
        $this->typeHint = new ReturnTypeInferer($this->reflection, $factory);
        $this->history = new History($collected, $class, $name);

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

    public function dontStub() {
        $this->setStubbed(false);
    }

    public function setStubbed($stubbed = true) {
        $this->stubbed = $stubbed;
    }

    public function isStubbed() {
        return $this->stubbed;
    }

    public function record($arguments, $returnValue, \Exception $thrown = null) {
        $this->history->add(new Call($this->named($arguments), $returnValue, $thrown));

        if ($this->checkReturnType && $thrown) {
            $this->checkException($thrown);
        } else if ($this->checkReturnType) {
            $this->checkReturnValue($returnValue);
        }
    }

    private function checkReturnValue($returnValue) {
        $type = $this->typeHint->getType();
        if (!$type->is($returnValue)) {
            $returned = $this->toString($returnValue);
            throw new \ReflectionException("[{$this->class}::{$this->name}()] returned [$returned] " .
                "which does not match its return type");
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

    public function enableReturnTypeChecking($enabled = true) {
        $this->checkReturnType = $enabled;
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

    /**
     * @return array|arguments\Argument[]
     */
    public function arguments() {
        return $this->arguments;
    }
}