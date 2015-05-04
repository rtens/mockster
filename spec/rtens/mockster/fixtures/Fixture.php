<?php
namespace spec\rtens\mockster\fixtures;

class Fixture {

    protected $undos = [];

    public function after() {
        foreach ($this->undos as $undo) {
            $undo();
        }
    }

}