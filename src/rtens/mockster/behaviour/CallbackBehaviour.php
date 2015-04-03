<?php
namespace rtens\mockster\behaviour;

class CallbackBehaviour extends Behaviour {

    /** @var callable */
    private $callback;

    /**
     * @param callable $callback
     */
    function __construct($callback) {
        $this->callback = $callback;
    }

    protected function doInvoke($args) {
        return call_user_func($this->callback, $args);
    }
}