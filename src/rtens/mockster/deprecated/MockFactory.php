<?php
namespace rtens\mockster\deprecated;

use watoki\factory\Factory;

class MockFactory extends Factory {

    function __construct() {
        parent::__construct();
        $this->setProvider('StdClass', new MockProvider($this));
    }

    /**
     * @param string $class
     * @param null|array $args
     * @return Mock
     */
    public function getInstance($class, $args = null) {
        return parent::getInstance($class, $args);
    }

    public function getMock($class) {
        return $this->getInstance($class);
    }

    public function getTestUnit($class, $args = array()) {
        return $this->getInstance($class, $args)->__mock()->makeTestUnit();
    }

}
?>
