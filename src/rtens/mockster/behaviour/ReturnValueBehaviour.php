<?php
namespace rtens\mockster\behaviour;

class ReturnValueBehaviour extends Behaviour {

    /** @var mixed */
    private $value;

    function __construct($value) {
        $this->value = $value;
    }

    protected function doInvoke($args) {
        return $this->value;
    }
}