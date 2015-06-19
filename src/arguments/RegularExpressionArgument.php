<?php
namespace rtens\mockster\arguments;

class RegularExpressionArgument extends Argument {

    private $expression;

    function __construct($expression) {
        $this->expression = $expression;
    }

    public function accepts(Argument $argument) {
        return $argument instanceof RegularExpressionArgument && $argument->expression == $this->expression
            || $argument instanceof ExactArgument && preg_match($this->expression, $argument->value()) === 1;
    }
}