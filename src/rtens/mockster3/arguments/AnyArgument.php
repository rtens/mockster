<?php
namespace rtens\mockster3\arguments;

class AnyArgument extends Argument {

    public function matches($value) {
        return true;
    }
}