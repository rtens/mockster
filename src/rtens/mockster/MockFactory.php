<?php
namespace rtens\mockster;

use watoki\factory\Factory;

class MockFactory extends Factory {

    function __construct() {
        parent::__construct();
        $this->setProvider('StdClass', new MockProvider($this));
    }

    public function getInstance($class, $args = null) {
        return parent::getInstance($class, $args);
    }

}
?>
