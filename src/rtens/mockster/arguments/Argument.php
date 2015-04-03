<?php
namespace rtens\mockster\arguments;

abstract class Argument {

    public static function any() {
        return new AnyArgument();
    }

    public static function exact($value) {
        return new ExactArgument($value);
    }

    abstract public function matches($value);
}