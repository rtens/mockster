<?php
namespace spec\rtens\mockster\fixtures;

use rtens\scrut\tests\statics\StaticTestSuite;

class Specification extends StaticTestSuite {

    protected function after() {
        parent::after();

        foreach ($this as $property) {
            if (method_exists($property, 'after')) {
                $property->after();
            }
        }
    }
}