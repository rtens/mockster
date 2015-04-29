<?php
namespace spec\rtens\mockster\fixtures;

use watoki\scrut\tests\migration\PhpUnitTestSuite;

/**
 * @property MockFactoryFixture fixture <-
 * @property \spec\rtens\mockster\fixtures\FilterFixture filter <-
 */
class Specification extends PhpUnitTestSuite {

    public $undos = [];

    protected function before() {
        parent::before();
        $this->fixture = new MockFactoryFixture($this);
        $this->filter = new FilterFixture($this);
    }

    protected function after() {
        foreach ($this->undos as $undo) {
            $undo();
        }
        parent::after();
    }

} 