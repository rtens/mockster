<?php
namespace rtens\mockster3\arguments;

class BooleanArgument extends Argument {

    public function accepts(Argument $argument) {
        return $argument instanceof BooleanArgument
        || $argument instanceof ExactArgument && is_bool($argument->value());
    }
}