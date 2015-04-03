<?php
namespace rtens\mockster;

use spec\rtens\mockster\FooMock;

class Mockster {

    /** @var string */
    private $class;

    /** @var \rtens\mockster\Stubs */
    private $stubs;

    /**
     * @param string $class The FQN of the class to mock
     */
    function __construct($class) {
        $this->class = $class;
        $this->stubs = new Stubs($class);
    }

    /**
     * @param Stub|mixed $call
     * @return Stub
     */
    public static function method(Stub $call) {
        return $call;
    }

    /**
     * @return object A mock-instance of the class
     */
    public function mock() {
        return new FooMock($this->stubs);
    }

    /**
     * Intercepts all method call and returns a corresponding MethodCall object
     *
     * @param string $name
     * @param array $arguments
     * @return Stub
     */
    function __call($name, $arguments) {
        return $this->stubs->add($name, $arguments);
    }


}