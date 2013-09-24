<?php
namespace rtens\mockster;
 
use watoki\factory\Injector;
use watoki\factory\Provider;

class MockProvider implements Provider {

    private static $mockCodes = array();

    /** @var Injector */
    protected $injector;

    private $factory;

    public function __construct(MockFactory $factory) {
        $this->injector = new Injector($factory);
        $this->factory = $factory;
    }

    /**
     * @param string $classname Fully qualified name of the class or interface to mock.
     * @param null|array $constructorArgs Arguments for the constructor (as list or map). If null, the constructor is not invoked.
     * @throws \InvalidArgumentException
     * @return \rtens\mockster\Mock
     */
    public function provide($classname, array $constructorArgs = null) {
        $callConstructor = $constructorArgs !== null;
        $mockClassName = $this->makeMockClassName($classname, $callConstructor);

        $code = $this->getMockCode($classname, $mockClassName, $callConstructor);

        if (!class_exists($mockClassName)) {
            eval($code);
        }

        $mockClassReflection = new \ReflectionClass($mockClassName);
        if ($callConstructor && $mockClassReflection->getConstructor()) {
            $constructorArgs = $this->injector->injectMethodArguments($mockClassReflection->getConstructor(), $constructorArgs);
        }
        $instance = $this->injector->injectConstructor($mockClassName, $callConstructor ? $constructorArgs : array());

        $mockster = new Mockster($this->factory, $classname, $instance, $constructorArgs, $code);
        $this->setMockster($instance, $mockster);

        return $instance;
    }

    private function makeMockClassName($classname, $callConstructor) {
        $mockClassName = 'Mock_' . str_replace('\\', '_', $classname);
        if (!$callConstructor) {
            $mockClassName .= '_NoConstructor';
        }
        return $mockClassName;
    }

    private function getMockCode($classname, $mockClassName, $callConstructor) {
        if (!array_key_exists($mockClassName, self::$mockCodes)) {
            self::$mockCodes[$mockClassName] = $this->generateMockCode($classname, $mockClassName, $callConstructor);
        }

        return self::$mockCodes[$mockClassName];
    }

    private function setMockster($instance, Mockster $mockster) {
        $reflection = new \ReflectionClass($instance);
        $mockProperty = $reflection->getProperty('__mock');
        $mockProperty->setAccessible(true);
        $mockProperty->setValue($instance, $mockster);
    }

    /**
     * Overwrites all protected and public methods of the mocked class.
     *
     * @param \ReflectionClass $classReflection
     * @return string Of all method definitions of the mock class
     */
    private function getMethodDefinitions(\ReflectionClass $classReflection) {
        $methods = '';

        foreach ($classReflection->getMethods() as $method) {
            if (!$this->isMockable($method)) {
                continue;
            }

            $methodName = $method->getName();

            $params = array();
            $args = array();

            foreach ($method->getParameters() as $param) {
                $typeHint = '';
                $default = '';
                $reference = '';

                if ($param->isArray()) {
                    $typeHint = 'array ';
                } else {
                    try {
                        $class = $param->getClass();
                    } catch (\ReflectionException $e) {
                        $class = FALSE;
                    }

                    if ($class) {
                        $typeHint = $class->getName() . ' ';
                    }
                }

                if ($param->isDefaultValueAvailable()) {
                    $value = $param->getDefaultValue();
                    $default = ' = ' . var_export($value, TRUE);
                } else if ($param->isOptional()) {
                    $default = ' = null';
                }

                if ($param->isPassedByReference()) {
                    $reference = '&';
                }

                $paramString = $typeHint . $reference . '$' . $param->getName() . $default;
                $params[] = $paramString;
                $args[] = '$' . $param->getName();
            }
            $paramsString = implode(', ', $params);
            $argsString = implode(', ', $args);

            $isAbstract = $method->isAbstract() ? 'true' : 'false';

            $docComment = $method->getDocComment();

            $methods .= "

    $docComment
    public function $methodName ( $paramsString ) {
        if (!\$this->__mock()) {
            return parent::$methodName( $argsString );
        }

        \$method = \$this->__mock()->method('$methodName');

        if ($isAbstract || \$method->isMocked()) {
            return \$method->invoke(func_get_args());
        } else {
            \$value = parent::$methodName( $argsString );
            \$method->getHistory()->log(func_get_args(), \$value);
            return \$value;
        }
    }";
        }

        return $methods;
    }

    private function generateMockCode($classname, $mockClassName, $callConstructor) {
        $classReflection = new \ReflectionClass($classname);

        $implements = 'implements \rtens\mockster\Mock';
        $extends = '';

        if (interface_exists($classname)) {
            $implements = 'implements ' . $classname . ', \rtens\mockster\Mock';
        } else if (class_exists($classname)) {
            $extends = ' extends ' . $classname;
        }

        $methodDefs = $this->getMethodDefinitions($classReflection);

        $constuctorDef = '';
        if (!$callConstructor) {
            $constuctorDef = 'public function __construct() {}';
        }

        return '
class ' . $mockClassName . ' ' . $extends . ' ' . $implements . ' {

    private $__mock;

    public function __mock() {
        return $this->__mock;
    }

    ' . $constuctorDef . '

    ' . $methodDefs . '

}';
    }

    private function isMockable(\ReflectionMethod $method) {
        return !($method->isStatic() || $method->isPrivate() || $method->isFinal() || $method->isConstructor());
    }
}
