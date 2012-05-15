<?php
namespace mockster\behaviour;

use mockster\Behaviour;

class CallbackBehaviour extends Behaviour {

    private $callback;

    public function __construct($callback) {
        $this->callback = $callback;
    }

    public function getReturnValue(array $arguments) {
        parent::getReturnValue($arguments);
        $callback = $this->callback;
        return call_user_func_array($callback, $arguments);
    }
}
?>
