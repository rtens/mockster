<?php
namespace rtens\mockster3;

use watoki\factory\Factory;
use watoki\factory\Injector;
use watoki\factory\Provider;

class MockProvider implements Provider {

    /** @var Injector */
    protected $injector;

    /** @var \watoki\factory\Factory */
    private $factory;

    public function __construct(Factory $factory) {
        $this->injector = new Injector($factory);
        $this->factory = $factory;
    }

    public function provide($className, array $constructorArgs = null) {
        $callConstructor = $constructorArgs !== null;
        $mockClassName = $this->makeMockClassName($className, $callConstructor);

        if (!class_exists($mockClassName)) {
            $generator = new MockGenerator();
            $code = $generator->generateMock($className, $mockClassName, $callConstructor);
            eval($code);
        }

        $instance = $this->injector->injectConstructor($mockClassName, $callConstructor ? $constructorArgs : array());
        $this->injector->injectProperties($instance, function () {
            return true;
        });
        $instance->__stubs = new Stubs($className, $this->factory);
        return $instance;
    }

    private function makeMockClassName($classname, $callConstructor) {
        $mockClassName = 'Mock_' . str_replace('\\', '_', $classname);
        if (!$callConstructor) {
            $mockClassName .= '_NoConstructor';
        }
        return $mockClassName;
    }
}