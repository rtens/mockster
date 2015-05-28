<?php
namespace rtens\mockster;

use watoki\reflect\ValuePrinter;

class History {

    /** @var Call[] */
    private $calls = [];

    /** @var History[] */
    private $collected;

    /** @var string */
    private $class;

    /** @var string */
    private $methodName;

    function __construct($collectedHistory, $class, $methodName) {
        $this->collected = $collectedHistory;
        $this->class = $class;
        $this->methodName = $methodName;
    }

    public function add(Call $call) {
        $this->calls[] = $call;
    }

    /**
     * @return array|Call[]
     */
    public function calls() {
        $calls = $this->calls;
        foreach ($this->collected as $history) {
            $calls = array_merge($calls, $history->calls());
        }
        return $calls;
    }

    /**
     * @param null|int $times
     * @return bool
     */
    public function beenCalled($times = null) {
        if (is_null($times)) {
            return !empty($this->calls());
        }
        return count($this->calls()) == $times;
    }

    /**
     * @param int $index
     * @return Call
     */
    public function inCall($index) {
        $calls = $this->calls();
        if (!array_key_exists($index, $calls)) {
            throw new \InvalidArgumentException("No call [$index] recorded.");
        }
        return $calls[$index];
    }

    /**
     * @return string The printed history
     */
    public function printedHistory() {
        $calls = [];
        foreach ($this->calls() as $call) {
            $calls[] = $this->printCall($call);
        }

        if (!$calls) {
            return "No calls recorded for [{$this->class}::{$this->methodName}()]";
        }

        return "History of [{$this->class}::{$this->methodName}()]\n  " . implode("\n  ", $calls);
    }

    private function toString($value) {
        return ValuePrinter::serialize($value);
    }

    private function printCall(Call $call) {
        return "{$this->methodName}("
        . implode(', ', $this->printArguments($call)) . ') '
        . $this->printResult($call);
    }

    private function printArguments(Call $call) {
        $arguments = [];
        foreach ($call->arguments() as $i => $argument) {
            if (!is_numeric($i)) {
                continue;
            }
            $arguments[] = $this->toString($argument);
        }
        return $arguments;
    }

    private function printResult(Call $call) {
        if ($call->thrown()) {
            $result = '!! ' . get_class($call->thrown())
                . '(' . $this->toString($call->thrown()->getMessage()) . ')';
            return $result;
        } else {
            $result = '-> ' . $this->toString($call->returned());
            return $result;
        }
    }
}