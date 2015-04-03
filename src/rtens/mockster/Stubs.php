<?php
namespace rtens\mockster;

use rtens\mockster\exceptions\UndefinedBehaviourException;

class Stubs {

    /** @var array|Stub[][] */
    private $stubs = [];

    /** @var string */
    private $class;

    /**
     * @param string $class
     */
    function __construct($class) {
        $this->class = $class;
    }

    public function add($name, $arguments) {
        try {
            $stub = $this->find($name, $arguments);
        } catch (UndefinedBehaviourException $e) {
            $stub = new Stub($this->class, $name, $arguments);
            $this->stubs[$name][] = $stub;
        }

        return $stub;
    }

    /**
     * @param string $name
     * @param array $args
     * @throws exceptions\UndefinedBehaviourException
     * @return Stub
     */
    public function find($name, $args) {
        if (array_key_exists($name, $this->stubs)) {
            foreach ($this->stubs[$name] as $stub) {
                /** @var Stub $stub */
                if ($stub->matches($args)) {
                    return $stub;
                }
            }
        }
        throw new UndefinedBehaviourException("No stub set for [$this->class::$name()] with " . json_encode($args));
    }
}