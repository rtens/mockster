<?php
namespace rtens\mockster\deprecated\behaviour;

use rtens\mockster\deprecated\Behaviour;

class CallbackBehaviour extends Behaviour {

    private $callback;

    public function __construct($callback) {
        $this->callback = $callback;
    }

    public function getReturnValue(array $arguments) {
        parent::getReturnValue($arguments);
        return call_user_func_array($this->callback, $arguments);
    }
}
?>
