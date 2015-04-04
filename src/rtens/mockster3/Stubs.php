<?php
namespace rtens\mockster3;

use rtens\mockster3\exceptions\UndefinedBehaviourException;

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
        if (array_key_exists($name, $this->stubs)) {
            foreach ($this->stubs[$name] as $stub) {
                /** @var Stub $stub */
                if ($stub->matches($arguments)) {
                    return $stub;
                }
            }
        }

        $stub = new Stub($this->class, $name, $arguments);
        $this->stubs[$name][] = $stub;
        return $stub;
    }
}