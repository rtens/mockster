<?php
namespace rtens\mockster3;

class Call {

    private $arguments;
    private $returnValue;
    private $thrownException;

    function __construct($arguments, $returnValue, \Exception $thrownException = null) {
        $this->arguments = $arguments;
        $this->returnValue = $returnValue;
        $this->thrownException = $thrownException;
    }

    /**
     * @param string|int $nameOrIndex
     * @return mixed
     */
    public function argument($nameOrIndex) {
        return $this->arguments[$nameOrIndex];
    }

    /**
     * @return array The arguments the call was made with indexed by position *and* name
     */
    public function arguments() {
        return $this->arguments;
    }

    /**
     * @return mixed The returned value
     */
    public function returned() {
        return $this->returnValue;
    }

    /**
     * @return \Exception|null The thrown Exception (if any)
     */
    public function thrown() {
        return $this->thrownException;
    }

    /**
     * @param callable $callback Is invoked with the arguments of the call
     */
    public function recorded(callable $callback) {
        $arguments = [];
        foreach ($this->arguments as $i => $argument) {
            if (is_numeric($i)) {
                $arguments[] = $argument;
            }
        }
        call_user_func_array($callback, $arguments);
    }
}