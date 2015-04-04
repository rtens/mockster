<?php
namespace rtens\mockster;

use watoki\factory\Provider;

class MockProvider implements Provider {

    public function provide($classname, array $constructorArgs = array()) {
        $callConstructor = $constructorArgs !== null;
        $mockClassName = $this->makeMockClassName($classname, $callConstructor);

        if (!class_exists($mockClassName)) {
            $generator = new MockGenerator();
            $code = $generator->generateMock($classname, $mockClassName, $callConstructor);
            eval($code);
        }

        return new $mockClassName;
    }

    private function makeMockClassName($classname, $callConstructor) {
        $mockClassName = 'Mock_' . str_replace('\\', '_', $classname);
        if (!$callConstructor) {
            $mockClassName .= '_NoConstructor';
        }
        return $mockClassName;
    }
}