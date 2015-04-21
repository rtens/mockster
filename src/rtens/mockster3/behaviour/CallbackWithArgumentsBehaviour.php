<?php
namespace rtens\mockster3\behaviour;

class CallbackWithArgumentsBehaviour extends Behaviour{

    /** @var callable */
    private $callbackWithArguments;

    /**
     * @param callable $callbackWithArguments
     */
    function __construct($callbackWithArguments) {
        $this->callbackWithArguments = $callbackWithArguments;
    }

    protected function doInvoke($args) {
        return call_user_func_array($this->callbackWithArguments, $args);
    }
}