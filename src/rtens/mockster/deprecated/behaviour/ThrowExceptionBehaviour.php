<?php
namespace rtens\mockster\deprecated\behaviour;

use rtens\mockster\deprecated\Behaviour;

class ThrowExceptionBehaviour extends Behaviour {

    private $exception;

    public function __construct($exception) {
        $this->exception = $exception;
    }

    public function getReturnValue(array $arguments) {
        parent::getReturnValue($arguments);
        throw $this->exception;
    }
}
?>
