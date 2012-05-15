<?php
namespace mockster\behaviour;

use mockster\Behaviour;

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
