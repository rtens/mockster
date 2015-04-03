<?php
namespace rtens\mockster;

class Mockster3 {

    /**
     * @param MethodCall|mixed $call
     * @return MethodCall
     */
    public static function method(MethodCall $call) {
        return $call;
    }
}