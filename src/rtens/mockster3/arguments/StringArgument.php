<?php
namespace rtens\mockster3\arguments;

class StringArgument extends Argument {

    public function accepts(Argument $argument) {
        return $argument instanceof StringArgument
        || $argument instanceof ExactArgument && is_string($argument->value());
    }
}