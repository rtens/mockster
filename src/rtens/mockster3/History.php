<?php
namespace rtens\mockster3;

class History {

    /** @var Call[] */
    private $calls = [];

    /** @var History[] */
    private $collected;

    function __construct($collectedHistory) {
        $this->collected = $collectedHistory;
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
}