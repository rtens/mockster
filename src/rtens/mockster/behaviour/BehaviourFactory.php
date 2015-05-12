<?php
namespace rtens\mockster\behaviour;

class BehaviourFactory {

    /** @var callable */
    private $listener;

    /**
     * @param callable $listener Invoked with the selected Behaviour
     */
    function __construct($listener) {
        $this->listener = $listener;
    }

    private function set(Behaviour $behaviour) {
        call_user_func($this->listener, $behaviour);
        return $behaviour;
    }

    /**
     * @param mixed $value
     * @return Behaviour
     */
    public function return_($value) {
        return $this->set(new ReturnValueBehaviour($value));
    }

    /**
     * @param \Exception $exception
     * @return Behaviour
     */
    public function throw_($exception) {
        return $this->set(new ThrowExceptionBehaviour($exception));
    }

    public function call($callback) {
        return $this->set(new CallbackBehaviour($callback));
    }

    public function forwardTo($callbackWithArguments) {
        return $this->set(new CallbackWithArgumentsBehaviour($callbackWithArguments));
    }
}