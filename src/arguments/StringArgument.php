<?php
namespace rtens\mockster\arguments;

class StringArgument extends Argument {

    public function accepts(Argument $argument) {
        return $argument instanceof StringArgument
        || $argument instanceof ExactArgument && is_string($argument->value());
    }
}