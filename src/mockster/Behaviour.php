<?php
namespace mockster;

abstract class Behaviour {

    private $timesCalled = 0;
    private $maxCalls;
    private $arguments;

    /**
     * @param array $arguments
     * @return mixed
     */
    public function getReturnValue(array $arguments) {
        $this->timesCalled++;
        return null;
    }

    /**
     * @param array $arguments
     * @return boolean
     */
    public function appliesTo(array $arguments) {
        return ((!isset($this->maxCalls) || $this->timesCalled < $this->maxCalls)
                && (!isset($this->arguments) || $arguments == $this->arguments));
    }

    /**
     * @return Behaviour
     */
    public function once() {
        return $this->times(1);
    }

    /**
     * @param int $x
     * @return Behaviour
     */
    public function times($x) {
        $this->maxCalls = $x;
        return $this;
    }

    /**
     * @return Behaviour
     */
    public function withArguments() {
        $this->arguments = func_get_args();
        return $this;
    }
}
?>
