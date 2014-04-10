<?php
namespace rtens\mockster;

class StubRegistry {

    /** @var array|Method[] */
    private $stubs = array();

    /**
     * @param string $methodName
     * @param Method $stub
     */
    public function set($methodName, Method $stub) {
        $this->stubs[$methodName] = $stub;
    }

    /**
     * Checks if a stub for the method exists
     *
     * @param string $methodName
     * @return bool
     */
    public function exists($methodName) {
        return array_key_exists($methodName, $this->stubs);
    }

    /**
     * @param string $methodName
     * @return null|Method
     */
    public function get($methodName) {
        return $this->exists($methodName) ? $this->stubs[$methodName] : null;
    }

    /**
     * @return array|Method[]
     */
    public function toArray() {
        return $this->stubs;
    }

} 