<?php
namespace rtens\mockster;

use spec\rtens\mockster\FooMock;

class Mockster {

    /** @var string */
    private $class;

    /**
     * @param MethodCall|mixed $call
     * @return MethodCall
     */
    public static function method(MethodCall $call) {
        return $call;
    }

    /**
     * @param string $class The FQN of the class to mock
     */
    function __construct($class) {
        $this->class = $class;
    }

    /**
     * @return object A mock-instance of the class
     */
    public function mock() {
        return new FooMock($this);
    }

    /**
     * Intercepts all method call and returns a corresponding MethodCall object
     *
     * @param string $name
     * @param array $arguments
     * @return MethodCall
     */
    function __call($name, $arguments) {
        return new MethodCall($name, $arguments);
    }


}