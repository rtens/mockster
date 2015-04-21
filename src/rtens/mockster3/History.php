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
        return $this->makeUnique($calls);
    }

    private function makeUnique($calls) {
        $unique = [];
        foreach ($calls as $call) {
            $unique[spl_object_hash($call)] = $call;
        }
        return array_values($unique);
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
    public function call($index) {
        return $this->calls()[$index];
    }
}