<?php
namespace rtens\mockster3\arguments;

class ObjectArgument extends Argument {

    private $class;

    function __construct($class) {
        $this->class = $class;
    }

    public function accepts(Argument $argument) {
        return $argument instanceof ExactArgument && is_a($argument->value(), $this->class)
            || $argument instanceof ObjectArgument && $argument->class == $this->class
            || $argument instanceof ObjectArgument && is_subclass_of($argument->class, $this->class);
    }
}