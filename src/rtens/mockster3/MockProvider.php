<?php
namespace rtens\mockster3;

use watoki\factory\Factory;
use watoki\factory\Injector;
use watoki\factory\Provider;

class MockProvider implements Provider {

    public static $NO_CONSTRUCTOR = ['NO_CONSTRUCTOR'];

    /** @var Injector */
    protected $injector;

    /** @var \watoki\factory\Factory */
    private $factory;

    /** @var callable */
    private $parameterFilter;

    public function __construct(Factory $factory) {
        $this->injector = new Injector($factory);
        $this->factory = $factory;

        $this->parameterFilter = function () {
            return true;
        };
    }

    public function provide($className, array $constructorArgs = []) {
        $callConstructor = $constructorArgs != self::$NO_CONSTRUCTOR;;
        $mockClassName = $this->makeMockClassName($className, $callConstructor);

        if (!class_exists($mockClassName)) {
            $generator = new MockGenerator();
            $code = $generator->generateMock($className, $mockClassName, $callConstructor);
            eval($code);
        }

        return $this->injector->injectConstructor($mockClassName, $callConstructor ? $constructorArgs : array(), $this->parameterFilter);
    }

    private function makeMockClassName($classname, $callConstructor) {
        $mockClassName = 'Mock_' . str_replace('\\', '_', $classname);
        if (!$callConstructor) {
            $mockClassName .= '_NoConstructor';
        }
        return $mockClassName;
    }

    /**
     * @param callable $filter
     */
    public function setParameterFilter($filter) {
        $this->parameterFilter = $filter;
    }
}