<?php
namespace rtens\mockster\behaviour;

use rtens\mockster\Behaviour;

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
