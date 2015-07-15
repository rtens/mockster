<?php
namespace rtens\mockster\arguments;

abstract class Argument {

    /**
     * @return mixed|AnyArgument
     */
    public static function any() {
        return new AnyArgument();
    }

    /**
     * @param mixed $value
     * @return mixed|ExactArgument
     */
    public static function exact($value) {
        return new ExactArgument($value);
    }

    /**
     * @return mixed|StringArgument
     */
    public static function string() {
        return new StringArgument();
    }

    /**
     * @return mixed|IntegerArgument
     */
    public static function integer() {
        return new IntegerArgument();
    }

    /**
     * @return mixed|BooleanArgument
     */
    public static function boolean() {
        return new BooleanArgument();
    }

    /**
     * @param string $class
     * @return mixed|ObjectArgument
     */
    public static function object($class) {
        return new ObjectArgument($class);
    }

    /**
     * @param string $expression
     * @return mixed|RegularExpressionArgument
     */
    public static function regex($expression) {
        return new RegularExpressionArgument($expression);
    }

    /**
     * @param callable $accepts
     * @return mixed|CallbackArgument
     */
    public static function callback(callable $accepts) {
        return new CallbackArgument($accepts);
    }

    /**
     * @param $needle
     * @return mixed|ContainingArgument
     */
    public static function contains($needle) {
        return new ContainingArgument($needle);
    }

    /**
     * @param Argument $argument
     * @return boolean
     */
    abstract public function accepts(Argument $argument);
}