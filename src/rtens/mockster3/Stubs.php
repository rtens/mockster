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
        $arguments = $this->normalize($arguments);

        if (array_key_exists($name, $this->stubs)) {
            $collected = false;
            $collectStub = new CollectStub($this->class, $name, $arguments, $this->factory);
            $collectStub->setStubbed($this->defaultStubbing);

            foreach ($this->stubs[$name] as $stub) {
                /** @var Stub $stub */
                if ($stub->arguments() == $arguments) {
                    return $stub;
                } else if ($this->accept($arguments, $stub->arguments())) {
                    $collected = true;
                    $collectStub->add($stub);
                }
            }

            if ($collected) {
                $this->stubs[$name][] = $collectStub;
                return $collectStub;
            }
        }

        return $this->addStub($name, $arguments);
    }

    public function find($name, $arguments) {
        $arguments = $this->normalize($arguments);

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