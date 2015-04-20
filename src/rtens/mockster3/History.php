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

    public function calls() {
        $calls = $this->calls;
        foreach ($this->collected as $history) {
            $calls = array_merge($calls, $history->calls());
        }
        return $calls;
    }

    public function beenCalled($times = null) {
        if (is_null($times)) {
            return !empty($this->calls());
        }
        return count($this->calls()) == $times;
    }

    public function call($int) {
        return $this->calls()[$int];
    }
}