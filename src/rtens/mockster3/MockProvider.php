<?php
namespace rtens\mockster3;

use watoki\factory\Provider;

class MockProvider implements Provider {

    public function provide($className, array $constructorArgs = null) {
        $callConstructor = $constructorArgs !== null;
        $mockClassName = $this->makeMockClassName($className, $callConstructor);

        if (!class_exists($mockClassName)) {
            $generator = new MockGenerator();
            $code = $generator->generateMock($className, $mockClassName, $callConstructor);
            eval($code);
        }

        return (new \ReflectionClass($mockClassName))->newInstanceArgs((array)$constructorArgs);
    }

    private function makeMockClassName($classname, $callConstructor) {
        $mockClassName = 'Mock_' . str_replace('\\', '_', $classname);
        if (!$callConstructor) {
            $mockClassName .= '_NoConstructor';
        }
        return $mockClassName;
    }
}