<?php namespace rtens\mockster;

use watoki\reflect\ValuePrinter;

class CallAssertion {

    /** @var Call */
    private $call;

    /** @var string  */
    private $methodCall;

    /**
     * @param string $methodCall
     * @param Call $call
     */
    public function __construct($methodCall, Call $call) {
        $this->call = $call;
        $this->methodCall = $methodCall;
    }

    /**
     * @param mixed $value The expected returned value
     * @throws AssertionFailedException If returned value is not $value
     */
    public function returned($value) {
        if ($value != $this->call->returned()) {
            throw new AssertionFailedException(
                $this->methodCall . ' did not return ' . ValuePrinter::serialize($value) . "\n" .
                ' The returned value was ' . ValuePrinter::serialize($this->call->returned()));
        }
    }

    /**
     * @param \Exception The expected thrown Exception
     * @throws AssertionFailedException If thrown exception is not $exception
     */
    public function thrown(\Exception $exception) {
        if (!$this->call->thrown()) {
            throw new AssertionFailedException(
                $this->methodCall . ' did not throw ' . ValuePrinter::serialize($exception) . "\n" .
                ' No exception was thrown.');
        }

        if (get_class($exception) != get_class($this->call->thrown()) || $exception->getMessage() != $this->call->thrown()->getMessage()) {
            throw new AssertionFailedException(
                $this->methodCall . ' did not throw ' . ValuePrinter::serialize($exception) . "\n" .
                ' The thrown exception was ' . ValuePrinter::serialize($this->call->thrown()));
        }
    }
}