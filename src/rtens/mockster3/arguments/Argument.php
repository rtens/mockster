<?php
namespace rtens\mockster3\arguments;

abstract class Argument {

    public static function any() {
        return new AnyArgument();
    }

    public static function exact($value) {
        return new ExactArgument($value);
    }

    public static function string() {
        return new StringArgument();
    }

    public static function integer() {
        return new IntegerArgument();
    }

    abstract public function accepts(Argument $argument);
}