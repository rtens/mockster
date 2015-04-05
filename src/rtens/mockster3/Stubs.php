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

    /** @var \watoki\factory\Factory */
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
        $arguments = $this->normalize($name, $arguments);
        $collected = [];

        if (array_key_exists($name, $this->stubs)) {
            foreach ($this->stubs[$name] as $stub) {
                /** @var Stub $stub */
                if ($stub->arguments() == $arguments) {
                    return $stub;
                } else if ($this->accept($arguments, $stub->arguments())) {
                    $collected[] = $stub;
                }
            }
        }

        return $this->addStub($name, $arguments, $collected);
    }

    public function find($name, $arguments) {
        $arguments = $this->normalize($name, $arguments);

        if (array_key_exists($name, $this->stubs)) {
            foreach ($this->stubs[$name] as $stub) {
                /** @var Stub $stub */
                if ($this->accept($stub->arguments(), $arguments)) {
                    return $stub;
                }
            }
        }

        return $this->addStub($name, $arguments);
    }

    private function addStub($name, $arguments, $collected = []) {
        $stub = new Stub($this->factory, $this->class, $name, $arguments, $collected);
        $stub->setStubbed($this->defaultStubbing);
        $this->stubs[$name][] = $stub;
        return $stub;
    }

    public function stubbedByDefault($stubbed = true) {
        $this->defaultStubbing = $stubbed;
    }

    /**
     * @param $method
     * @param $arguments
     * @return array|Argument[]
     */
    private function normalize($method, $arguments) {
        $normalized = [];

        $reflection = new \ReflectionMethod($this->class, $method);
        foreach ($reflection->getParameters() as $i => $parameter) {
            if (array_key_exists($i, $arguments)) {
                $argument = $arguments[$i];
                if ($argument instanceof Argument) {
                    $normalized[] = $argument;
                } else {
                    $normalized[] = Argument::exact($argument);
                }
            } else if ($parameter->isDefaultValueAvailable()) {
                $normalized[] = Argument::exact($parameter->getDefaultValue());
            }
        }

        return $normalized;
    }

    /**
     * @param Argument[] $a
     * @param Argument[] $b
     * @return bool
     */
    private function accept($a, $b) {
        if (count($a) != count($b)) {
            return false;
        }

        foreach ($a as $i => $argument) {
            if (!$argument->accepts($b[$i])) {
                return false;
            }
        }
        return true;
    }
}