<?php
namespace rtens\mockster;

class Stubs {

    /** @var array|Stub[][] */
    private $calls = [];

    /** @var string */
    private $class;

    /**
     * @param string $class
     */
    function __construct($class) {
        $this->class = $class;
    }

    public function invoke($name) {
        return $this->find($name)->invoke();
    }

    public function add($name, $arguments) {
        if (!array_key_exists($name, $this->calls)) {
            $this->calls[$name] = new Stub($this->class, $name, $arguments);
        }
        return $this->calls[$name];
    }

    /**
     * @param string $name
     * @return Stub
     */
    private function find($name) {
        return $this->calls[$name];
    }
}