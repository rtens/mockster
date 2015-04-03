<?php
namespace rtens\mockster\arguments;

class AnyArgument extends Argument {

    public function matches($value) {
        return true;
    }
}