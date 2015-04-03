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

    /**
     * @param string $name Of the method
     * @param array $args Indexed by position and name
     * @return mixed Return value of stub behaviour
     */
    public function invoke($name, $args) {
        return $this->find($name)->invoke($args);
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