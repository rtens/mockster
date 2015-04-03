<?php
namespace rtens\mockster\behaviour;

abstract class Behaviour {

    /** @var null|int */
    private $callsLeft;

    abstract protected function doInvoke();

    public function invoke() {
        if ($this->callsLeft !== null) {
            $this->callsLeft--;
        }
        return $this->doInvoke();
    }

    public function isActive() {
        return $this->callsLeft === null || $this->callsLeft > 0;
    }

    public function once() {
        $this->times(1);
    }

    /**
     * @param int $number
     */
    private function times($number) {
        $this->callsLeft = $number;
    }
}