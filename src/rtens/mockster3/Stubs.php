<?php
namespace rtens\mockster3;

use rtens\mockster3\arguments\Argument;
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
        $arguments = $this->normalize($arguments);

        if (array_key_exists($name, $this->stubs)) {
            foreach ($this->stubs[$name] as $stub) {
                /** @var Stub $stub */
                if ($stub->arguments() == $arguments) {
                    return $stub;
                }
            }
        }

        return $this->addStub($name, $arguments);
    }

    public function find($name, $arguments) {
        if (array_key_exists($name, $this->stubs)) {
            foreach ($this->stubs[$name] as $stub) {
                /** @var Stub $stub */
                if ($stub->matches($arguments)) {
                    return $stub;
                }
            }
        }

        return $this->addStub($name, $this->normalize($arguments));
    }

    private function addStub($name, $arguments) {
        $stub = new Stub($this->class, $name, $arguments, $this->factory);
        $stub->setStubbed($this->defaultStubbing);
        $this->stubs[$name][] = $stub;
        return $stub;
    }

    public function stubbedByDefault($stubbed = true) {
        $this->defaultStubbing = $stubbed;
    }

    /**
     * @param $arguments
     * @return array|Argument[]
     */
    private function normalize($arguments) {
        return array_map(function ($arg) {
            if ($arg instanceof Argument) {
                return $arg;
            } else {
                return Argument::exact($arg);
            }
        }, $arguments);
    }
}