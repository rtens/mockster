<?php
namespace rtens\mockster\behaviour;

use rtens\mockster\Behaviour;

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
