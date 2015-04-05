<?php
namespace rtens\mockster3;

use rtens\mockster3\arguments\Argument;
use rtens\mockster3\behaviour\Behaviour;
use rtens\mockster3\behaviour\BehaviourFactory;
use rtens\mockster3\exceptions\UndefinedBehaviourException;
use watoki\factory\Factory;

class Stub {

    /** @var string */
    private $name;

    /** @var array|Argument[] */
    private $arguments;

    /** @var Behaviour */
    private $behaviour;

    /** @var string */
    private $class;

    /** @var boolean */
    private $stubbed = true;

    /** @var \ReflectionMethod */
    private $reflection;

    /** @var ReturnTypeInferer */
    private $typeHint;

    /** @var \watoki\factory\Factory */
    private $factory;

    /** @var Call[] */
    private $calls = [];

    /** @var Stub[] */
    private $collected;

    /**
     * @param Factory $factory
     * @param string $class
     * @param string $name
     * @param array|Argument[] $arguments
     * @param array|Stub[] $collected
     */
    function __construct(Factory $factory, $class, $name, array $arguments = [], array $collected = []) {
        $this->factory = $factory;
        $this->class = $class;
        $this->name = $name;
        $this->arguments = $arguments;
        $this->collected = $collected;

        $this->reflection = new \ReflectionMethod($class, $name);
        $this->typeHint = new ReturnTypeInferer($this->reflection, $factory);
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
            $this->behaviour = $behaviour;
        });
    }

    /**
     * @param array $arguments Indexed by position and name
     * @throws exceptions\UndefinedBehaviourException
     * @return mixed The return value of the first active Behaviour
     */
    public function invoke($arguments) {
        if ($this->behaviour && $this->behaviour->isActive()) {
            return $this->behaviour->invoke($this->named($arguments));
        }

        try {
            return $this->typeHint->mockValue();
        } catch (\Exception $e) {
            throw new UndefinedBehaviourException("No active behaviour available for [$this->class::$this->name()] " .
                "and none could be inferred from return type hint.", 0, $e);
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
        $this->calls[] = new Call($this->named($arguments), $returnValue, $thrown);
    }

    /**
     * @return array|Call[]
     */
    public function calls() {
        $calls = $this->calls;
        foreach ($this->collected as $collected) {
            $calls = array_merge($calls, $collected->calls());
        }
        return $calls;
    }

    /**
     * @param int $index
     * @return Call
     */
    public function call($index) {
        return $this->calls()[$index];
    }

    private function named($arguments) {
        foreach ($this->reflection->getParameters() as $param) {
            if (array_key_exists($param->getPosition(), $arguments)) {
                $arguments[$param->getName()] = $arguments[$param->getPosition()];
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