<?php
namespace rtens\mockster\behaviour;

abstract class Behaviour {

    /** @var null|int */
    private $callsLeft;

    /** @var null|Behaviour */
    private $next;

    abstract protected function doInvoke($args);

    public function invoke($args) {
        if (!$this->hasCallsLeft() && $this->nextIsActive()) {
            return $this->next->invoke($args);
        }
        if ($this->callsLeft !== null) {
            $this->callsLeft--;
        }
        return $this->doInvoke($args);
    }

    public function isActive() {
        return $this->hasCallsLeft() || $this->nextIsActive();
    }

    public function once() {
        return $this->times(1);
    }

    /**
     * @param $number
     * @return Behaviour
     */
    public function times($number) {
        $this->callsLeft = $number;
        return $this;
    }

    public function then(Behaviour $behaviour = null) {
        if ($behaviour) {
            $this->next = $behaviour;
            return $behaviour;
        }
        return new BehaviourFactory(function (Behaviour $behaviour) {
            $this->next = $behaviour;
        });
    }

    /**
     * @return bool
     */
    private function hasCallsLeft() {
        return $this->callsLeft === null
        || $this->callsLeft > 0;
    }

    /**
     * @return bool
     */
    private function nextIsActive() {
        return $this->next && $this->next->isActive();
    }
}