<?php
namespace rtens\mockster;

use watoki\factory\Factory;
use watoki\factory\Injector;
use watoki\factory\Provider;

class MockProvider implements Provider {

    /** @var Injector */
    protected $injector;

    /** @var \watoki\factory\Factory */
    private $factory;

    /** @var callable */
    private $parameterFilter;

    /** @var Stubs */
    private $stubs;

    public function __construct(Factory $factory, Stubs $stubs) {
        $this->stubs = $stubs;
        $this->factory = $factory;
        $this->injector = new Injector($factory);

        $this->parameterFilter = function () {
            return true;
        };
    }

    public function provide($className, array $constructorArgs = null) {
        $callConstructor = $constructorArgs !== null;
        $mockClassName = $this->makeMockClassName($className, $callConstructor);

        if (!class_exists($mockClassName)) {
            $generator = new MockGenerator();
            $code = $generator->generateMock($className, $mockClassName, $callConstructor);
            eval($code);
        }

        $instance = $this->injector->injectConstructor($mockClassName, $callConstructor ? $constructorArgs : array(), $this->parameterFilter);
        $instance->__stubs = $this->stubs;
        return $instance;
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