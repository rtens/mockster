<?php
namespace rtens\mockster\behaviour;

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
        $arguments = [];
        foreach ($args as $i => $arg) {
            if (is_numeric($i)) {
                $arguments[] = $arg;
            }
        }
        return call_user_func_array($this->callbackWithArguments, $arguments);
    }
}