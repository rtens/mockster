<?php
namespace rtens\mockster\deprecated\behaviour;

use rtens\mockster\deprecated\Behaviour;

class ReturnValueBehaviour extends Behaviour {

    private $value;

    public function __construct($value) {
        $this->value = $value;
    }

    public function getReturnValue(array $arguments) {
        parent::getReturnValue($arguments);
        return $this->value;
    }
}
?>
