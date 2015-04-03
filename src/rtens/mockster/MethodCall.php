<?php
namespace rtens\mockster;

class MethodCall {

    /** @var string */
    private $name;

    /** @var array */
    private $arguments;

    /**
     * @param string $name
     * @param array $arguments
     */
    function __construct($name, array $arguments = []) {
        $this->name = $name;
        $this->arguments = $arguments;
    }

    public function willReturn($value) {
    }
}