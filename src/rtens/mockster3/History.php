<?php
namespace rtens\mockster3;

class History {

    /** @var Call[] */
    private $calls = [];

    /** @var Stub[] */
    private $collected;

    function __construct($collectedStubs) {
        $this->collected = $collectedStubs;
    }

    public function add(Call $call) {
        $this->calls[] = $call;
    }

    public function beenCalled($times = null) {
        if (is_null($times)) {
            return !empty($this->calls);
        }
        return count($this->calls) == $times;
    }

    public function call($int) {
        return $this->calls[$int];
    }
}