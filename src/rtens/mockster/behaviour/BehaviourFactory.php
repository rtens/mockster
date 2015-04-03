<?php
namespace rtens\mockster\behaviour;

use rtens\mockster\Stub;

class BehaviourFactory {

    /** @var Stub */
    private $stub;

    function __construct(Stub $stub) {
        $this->stub = $stub;
    }

    /**
     * @param mixed $value
     * @return Behaviour
     */
    public function return_($value) {
        return $this->stub->add(new ReturnValueBehaviour($value));
    }

    /**
     * @param \Exception $exception
     * @return Behaviour
     */
    public function throw_($exception) {
        return $this->stub->add(new ThrowExceptionBehaviour($exception));
    }

    public function call($callback) {
        return $this->stub->add(new CallbackBehaviour($callback));
    }

    public function forwardTo($callbackWithArguments) {
        return $this->stub->add(new CallbackWithArgumentsBehaviour($callbackWithArguments));
    }
}