<?php
namespace rtens\mockster3\arguments;

class ExactArgument extends Argument {

    private $value;

    function __construct($value) {
        $this->value = $value;
    }

    public function accepts(Argument $argument) {
        return $argument instanceof ExactArgument && $this->value === $argument->value;
    }

    /**
     * @return mixed
     */
    public function value() {
        return $this->value;
    }
}