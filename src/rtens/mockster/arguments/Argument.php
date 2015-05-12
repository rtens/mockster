<?php
namespace rtens\mockster\arguments;

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

    public static function boolean() {
        return new BooleanArgument();
    }

    public static function object($class) {
        return new ObjectArgument($class);
    }

    public static function regex($expression) {
        return new RegularExpressionArgument($expression);
    }

    public static function callback(callable $accepts) {
        return new CallbackArgument($accepts);
    }

    abstract public function accepts(Argument $argument);
}