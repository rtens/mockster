<?php
namespace rtens\mockster3;

use watoki\factory\Factory;

class CollectStub extends Stub {

    /** @var Stub[] */
    private $collected = [];

    function __construct($class, $name, array $arguments, Factory $factory) {
        parent::__construct($class, $name, $arguments, $factory);
    }

    public function add(Stub $stub) {
        $this->collected[] = $stub;
    }

    public function calls() {
        return array_merge(parent::calls(), array_map(function (Stub $stub) {
            return $stub->calls();
        }, $this->collected));
    }

}