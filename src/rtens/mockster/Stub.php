<?php
namespace rtens\mockster;

use rtens\mockster\behaviour\Behaviour;
use rtens\mockster\behaviour\BehaviourFactory;
use rtens\mockster\exceptions\UndefinedBehaviourException;

class Stub {

    /** @var string */
    private $name;

    /** @var array */
    private $arguments;

    /** @var Behaviour[] */
    private $behaviours = [];

    /** @var string */
    private $class;

    /**
     * @param string $class
     * @param string $name
     * @param array $arguments
     */
    function __construct($class, $name, array $arguments = []) {
        $this->class = $class;
        $this->name = $name;
        $this->arguments = $arguments;
    }

    /**
     * @param Behaviour|null $behaviour
     * @return Behaviour|BehaviourFactory
     */
    public function will(Behaviour $behaviour = null) {
        if (!$behaviour) {
            return new BehaviourFactory($this);
        }
        $this->behaviours[] = $behaviour;
        return $behaviour;
    }

    /**
     * @return mixed The return value of the first active Behaviour
     * @throws UndefinedBehaviourException
     */
    public function invoke() {
        foreach ($this->behaviours as $behaviour) {
            if ($behaviour->isActive()) {
                return $behaviour->invoke();
            }
        }
        var_dump($this->behaviours);
        throw new UndefinedBehaviourException("No active behaviour available for [$this->class::$this->name()]");
    }
}