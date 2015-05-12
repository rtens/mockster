<?php
namespace rtens\mockster\arguments;

class CallbackArgument extends Argument {

    private $callback;

    function __construct(callable $callback) {
        $this->callback = $callback;
    }

    public function accepts(Argument $argument) {
        return call_user_func($this->callback, $argument);
    }
}