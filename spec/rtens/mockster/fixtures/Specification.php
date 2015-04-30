<?php
namespace spec\rtens\mockster\fixtures;

use watoki\factory\Factory;
use watoki\scrut\TestName;
use watoki\scrut\tests\migration\PhpUnitTestSuite;

class Specification extends PhpUnitTestSuite {

    public $undos = [];

    /**
     * @param Factory $factory <-
     * @param TestName $parent
     * @throws \Exception
     */
    function __construct(Factory $factory, TestName $parent = null) {
        parent::__construct($factory, $parent);
        $factory->setSingleton(__CLASS__, $this);
    }

    protected function after() {
        foreach ($this->undos as $undo) {
            $undo();
        }
        parent::after();
    }

} 