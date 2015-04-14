<?php
namespace rtens\mockster3;

class Call {
    private $arguments;
    private $returnValue;
    private $thrownException;

    function __construct($arguments, $returnValue, \Exception $thrownException = null) {
        $this->arguments = $arguments;
        $this->returnValue = $returnValue;
        $this->thrownException = $thrownException;
    }

    public function argument($nameOrIndex) {
        return $this->arguments[$nameOrIndex];
    }

    public function returned() {
        return $this->returnValue;
    }

    public function threw() {
        return $this->thrownException;
    }
}