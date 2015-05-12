<?php
namespace rtens\mockster\arguments;

class AnyArgument extends Argument {

    public function accepts(Argument $argument) {
        return true;
    }
}