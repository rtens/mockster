<?php
namespace rtens\mockster3\arguments;

class AnyArgument extends Argument {

    public function accepts(Argument $argument) {
        return true;
    }
}