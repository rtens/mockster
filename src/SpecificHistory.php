<?php
namespace rtens\mockster;

use rtens\mockster\arguments\ExactArgument;

class SpecificHistory extends History {

    /** @var Stub */
    private $stub;

    /** @var \rtens\mockster\arguments\Argument[] */
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
                if (!$argument->accepts(new ExactArgument($call->argument($i)))) {
                    return false;
                }
            }
            return true;
        });
    }
}