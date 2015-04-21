<?php
namespace rtens\mockster3;

use rtens\mockster3\arguments\ExactArgument;

class SpecificHistory extends History {

    /** @var Stub */
    private $stub;

    /** @var \rtens\mockster3\arguments\Argument[] */
    private $arguments;

    function __construct(Stub $stub, $arguments) {
        parent::__construct([]);
        $this->stub = $stub;
        $this->arguments = $arguments;
    }

    /**
     * @return array|Call[]
     */
    public function calls() {
        return array_filter($this->stub->has()->calls(), function (Call $call) {
            foreach ($this->arguments as $i => $argument) {
                $callArguments = $call->arguments();
                if (!array_key_exists($i, $callArguments)
                    || !$argument->accepts(new ExactArgument($call->argument($i)))
                ) {
                    return false;
                }
            }
            return true;
        });
    }
}