<?php
namespace rtens\mockster;

use rtens\mockster\arguments\Argument;
use rtens\mockster\behaviour\Behaviour;
use rtens\mockster\behaviour\BehaviourFactory;
use rtens\mockster\exceptions\UndefinedBehaviourException;

class Stub {

    /** @var string */
    private $name;

    /** @var array|Argument[] */
    private $arguments;

    /** @var Behaviour[] */
    private $behaviours = [];

    /** @var string */
    private $class;

    /** @var boolean */
    private $stubbed = true;

    /**
     * @param string $class
     * @param string $name
     * @param array $arguments
     */
    function __construct($class, $name, array $arguments) {
        $this->class = $class;
        $this->name = $name;

        $arguments = array_map(function ($arg) {
            if (!($arg instanceof Argument)) {
                return Argument::exact($arg);
            } else {
                return $arg;
            }
        }, $arguments);
        $this->arguments = $arguments;
    }

    /**
     * @param Behaviour $behaviour
     * @return Behaviour
     */
    public function add(Behaviour $behaviour) {
        $this->behaviours[] = $behaviour;
        return $behaviour;
    }

    /**
     * @return BehaviourFactory
     */
    public function will() {
        return new BehaviourFactory($this);
    }

    /**
     * @param array $args Indexed by position and name
     * @throws exceptions\UndefinedBehaviourException
     * @return mixed The return value of the first active Behaviour
     */
    public function invoke($args) {
        foreach ($this->behaviours as $behaviour) {
            if ($behaviour->isActive()) {
                return $behaviour->invoke($args);
            }
        }
        var_dump($this->behaviours);
        throw new UndefinedBehaviourException("No active behaviour available for [$this->class::$this->name()]");
    }

    public function dontStub() {
        $this->stubbed = false;
    }

    public function isStubbed() {
        return $this->stubbed;
    }

    public function matches($arguments) {
        if (count($arguments) != count($this->arguments)) {
            return false;
        }

        foreach ($this->arguments as $i => $argument) {
            if (!$argument->matches($arguments[$i])) {
                return false;
            }
        }
        return true;
    }
}