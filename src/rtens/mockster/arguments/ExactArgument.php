<?php
namespace rtens\mockster\arguments;

class ExactArgument extends Argument {

    private $value;

    function __construct($value) {
        $this->value = $value;
    }

    public function matches($value) {
        return $this->value === $value;
    }
}