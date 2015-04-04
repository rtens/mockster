<?php
namespace rtens\mockster3;

use watoki\factory\Factory;

class Stubs {

    /** @var array|Stub[][] */
    private $stubs = [];

    /** @var string */
    private $class;

    /** @var bool */
    private $defaultStubbing = true;
    private $factory;

    /**
     * @param string $class
     * @param \watoki\factory\Factory $factory
     */
    function __construct($class, Factory $factory) {
        $this->class = $class;
        $this->factory = $factory;
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

        $stub = new Stub($this->class, $name, $arguments, $this->factory);
        $stub->setStubbed($this->defaultStubbing);
        $this->stubs[$name][] = $stub;
        return $stub;
    }

    public function stubbedByDefault($stubbed = true) {
        $this->defaultStubbing = $stubbed;
    }
}