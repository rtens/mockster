<?php
namespace rtens\mockster3\behaviour;

class BehaviourFactory {

    /** @var callable */
    private $setter;

    /**
     * @param callable $setter Invoked with the selected Behaviour
     */
    function __construct($setter) {
        $this->setter = $setter;
    }

    private function set(Behaviour $behaviour) {
        call_user_func($this->setter, $behaviour);
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