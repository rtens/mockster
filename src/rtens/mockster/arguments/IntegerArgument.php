<?php
namespace rtens\mockster\arguments;

class IntegerArgument extends Argument {

    public function accepts(Argument $argument) {
        return $argument instanceof IntegerArgument
        || $argument instanceof ExactArgument && is_int($argument->value());
    }
}