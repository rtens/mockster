<?php
namespace rtens\mockster;

class Stubs {

    /** @var array|Stub[] */
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
        if (!array_key_exists($name, $this->stubs)) {
            $this->stubs[$name] = new Stub($this->class, $name, $arguments);
        }
        return $this->stubs[$name];
    }

    /**
     * @param string $name
     * @return Stub
     */
    public function find($name) {
        return $this->stubs[$name];
    }
}