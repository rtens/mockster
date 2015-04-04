<?php
namespace rtens\mockster3;

class Stubs {

    /** @var array|Stub[][] */
    private $stubs = [];

    /** @var string */
    private $class;

    /** @var bool */
    private $defaultStubbing = true;

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
        $stub->setStubbed($this->defaultStubbing);
        $this->stubs[$name][] = $stub;
        return $stub;
    }

    public function stubbedByDefault($stubbed = true) {
        $this->defaultStubbing = $stubbed;
    }
}