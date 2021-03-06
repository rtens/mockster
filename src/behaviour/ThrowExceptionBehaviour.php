<?php
namespace rtens\mockster\behaviour;

class ThrowExceptionBehaviour extends Behaviour {

    /** @var \Exception */
    private $exception;

    /**
     * @param \Exception $exception
     */
    function __construct($exception) {
        $this->exception = $exception;
    }

    protected function doInvoke($args) {
        throw $this->exception;
    }
}