<?php
namespace rtens\mockster3\arguments;

class ExactArgument extends Argument {

    private $value;

    function __construct($value) {
        $this->value = $value;
    }

    public function accepts(Argument $argument) {
        if ($argument instanceof ExactArgument) {
            return $this->value === $argument->value;
        } else {
            return false;
        }
    }
}