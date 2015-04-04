<?php
namespace rtens\mockster;

abstract class Behaviour {

    private $timesCalled = 0;
    private $maxCalls;
    private $matcher;

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
        $matcher = $this->matcher;
        return ((!isset($this->maxCalls) || $this->timesCalled < $this->maxCalls)
            && (!isset($matcher) || $matcher($arguments)));
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
        $args = func_get_args();
        $this->matcher = function ($arguments) use ($args) {
            return array_values($arguments) == $args;
        };
        return $this;
    }

    public function with($args) {
        $this->matcher = function ($arguments) use ($args) {
            $values = array_values($arguments);
            foreach ($args as $name => $value) {
                if (array_key_exists($name, $arguments) && $arguments[$name] !== $value
                    || array_key_exists($name, $values) && $values[$name] !== $value
                ) {
                    return false;
                }
            }
            return true;
        };
        return $this;
    }

    public function when($matches) {
        $this->matcher = function ($arguments) use ($matches) {
            return call_user_func_array($matches, $arguments);
        };
        return $this;
    }
}

?>
