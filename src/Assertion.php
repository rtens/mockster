<?php namespace rtens\mockster;

use rtens\mockster\arguments\Argument;
use rtens\mockster\arguments\ExactArgument;
use watoki\reflect\ValuePrinter;

class Assertion {

    /** @var string */
    private $methodCall;

    /** @var History */
    private $history;

    /** @var Stub */
    private $stub;

    /** @var Mockster */
    private $mockster;

    /**
     * @param Stub $stub
     * @param Mockster $mockster
     * @param History $history
     */
    public function __construct(Stub $stub, Mockster $mockster, History $history) {
        $this->history = $history;
        $this->stub = $stub;
        $this->mockster = $mockster;

        $args = $this->printArguments($this->stub->arguments());
        $this->methodCall = $this->stub->className() . '::' . $this->stub->methodName() . "($args)";
    }

    public function beenCalled($times = null) {
        if (!$this->history->beenCalled($times)) {
            $times = $times ? (' ' . $times . ' time' . ($times != 1 ? 's' : '')) : '';
            throw new AssertionFailedException(
                $this->methodCall . " was not called" . $times . "\n" .
                (new HistoryPrinter())->printAll($this->mockster));
        }
    }

    /**
     * @param int $index
     * @return CallAssertion
     * @throws AssertionFailedException
     */
    public function inCall($index) {
        try {
            return new CallAssertion($this->methodCall, $this->history->inCall($index));
        } catch (\InvalidArgumentException $e) {
            throw new AssertionFailedException($e->getMessage() . "\n" .
                (new HistoryPrinter())->printAll($this->mockster));
        }
    }

    /**
     * @param Argument[] $arguments
     * @return string
     */
    private function printArguments($arguments) {
        return implode(', ', array_map(function (ExactArgument $argument) {
            return ValuePrinter::serialize($argument->value());
        }, array_filter($arguments, function (Argument $argument) {
            return $argument instanceof ExactArgument;
        })));
    }

}