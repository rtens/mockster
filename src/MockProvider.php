<?php
namespace rtens\mockster;

use watoki\factory\Factory;
use watoki\factory\Injector;
use watoki\factory\providers\DefaultProvider;

class MockProvider extends DefaultProvider {

    const NO_CONSTRUCTOR = ['__NO_CONSTRUCTOR__'];

    public function __construct(Factory $factory) {
        parent::__construct($factory);

        $this->injector = new Injector($factory, function ($class) use ($factory) {
            return (new Mockster($class, $factory))->mock();
        });
        $this->injector->setThrowWhenCantInjectProperty(false);

        $returnTrue = function () {
            return true;
        };
        $this->setParameterFilter($returnTrue);
        $this->setPropertyFilter($returnTrue);
        $this->setAnnotationFilter($returnTrue);
    }

    public function provide($className, array $constructorArgs = []) {
        $callConstructor = $constructorArgs != self::NO_CONSTRUCTOR;
        $mockClassName = $this->makeMockClassName($className, $callConstructor);

        if (!class_exists($mockClassName)) {
            $generator = new MockGenerator();
            $code = $generator->generateMock($className, $mockClassName, $callConstructor);
            eval($code);
        }

        if (!$callConstructor) {
            return $this->injector->injectConstructor($mockClassName, $constructorArgs, function () {
                return false;
            });
        }

        return parent::provide($mockClassName, $constructorArgs);
    }

    private function makeMockClassName($classname, $callConstructor) {
        $mockClassName = 'Mock_' . str_replace('\\', '_', $classname);
        if (!$callConstructor) {
            $mockClassName .= '_NoConstructor';
        }
        return $mockClassName;
    }
}