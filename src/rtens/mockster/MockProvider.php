<?php
namespace rtens\mockster;
 
use watoki\factory\Injector;
use watoki\factory\Provider;

class MockProvider implements Provider {

//    /**
//     * @var Generator
//     */
//    private $generator;

    private static $mockCodes = array();

    /** @var Injector */
    protected $injector;

    public function __construct(MockFactory $factory) {
        $this->injector = new Injector($factory);
        $this->factory = $factory;
//        $this->generator = new Generator($this);
    }

    /**
     * @param string $classname Fully qualified name of the class or interface to mock.
     * @param null|array $constructorArgs Arguments for the constructor (as list or map). If null, the constructor is not invoked.
     * @throws \InvalidArgumentException
     * @return \rtens\mockster\Mock
     */
    public function provide($classname, array $constructorArgs = null) {
        $mockClassName = 'Mock_' . str_replace('\\', '_', $classname);
        $callConstructor = $constructorArgs !== null;

        if (!$callConstructor) {
            $mockClassName .= '_NoConstructor';
        }

        $code = $this->getMockCode($classname, $mockClassName, $callConstructor);

        if (!class_exists($mockClassName)) {
            eval($code);
        }

        $mockClassReflection = new \ReflectionClass($mockClassName);

        if ($callConstructor && $mockClassReflection->getConstructor()) {
            $constructorArgs = $this->injector->injectMethodArguments($mockClassReflection->getConstructor(), $constructorArgs);
        }
        $instance = $this->injector->injectConstructor($mockClassName, $constructorArgs ?: array());

        $mockClassReflection->setStaticPropertyValue('__mockInstance', $instance);

        $mockProperty = $mockClassReflection->getProperty('__mock');
        $mockProperty->setAccessible(true);
        $mockProperty->setValue($instance, new Mockster($this->factory, $classname, $instance, $constructorArgs, $code));

        return $instance;
    }

    private function getMockCode($classname, $mockClassName, $callConstructor) {
        if (!array_key_exists($mockClassName, self::$mockCodes)) {
            self::$mockCodes[$mockClassName] = $this->generateMockCode($classname, $mockClassName, $callConstructor);
        }

        return self::$mockCodes[$mockClassName];
    }

    /**
     * Overwrites all protected properties for external access to dependencies
     *
     * @param \ReflectionClass $classReflection
     * @return string Of all properties definitions of the mock class
     */
    private function getPropertyDefinitions(\ReflectionClass $classReflection) {
        $defs = '';

        foreach ($classReflection->getProperties() as $propRefl) {
            if ($propRefl->isStatic() || !$propRefl->isProtected()) {
                continue;
            }

            $defs .= "

    public $" . $propRefl->getName() . ";";
        }

        return $defs;
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
            /** @var \ReflectionMethod $method */
            if ($method->isPrivate() || $method->isFinal() || $method->isConstructor()) {
                continue;
            }

            $methodName = $method->getName();

            $params = array();
            $args = array();

            foreach ($method->getParameters() as $param) {
                /** @var $param \ReflectionParameter */

                $typeHint = '';
                $default = '';
                $reference = '';

                if ($param->isArray()) {
                    $typeHint = 'array ';
                } else {
                    try {
                        /** @var $class \ReflectionClass */
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
            $static = $method->isStatic() ? 'static' : '';

            $object = $method->isStatic() ? 'self::$__mockInstance' : '$this';

            $docComment = $method->getDocComment();

            $methods .= "

    $docComment
    public $static function $methodName ( $paramsString ) {
        if (!{$object}->__mock()) {
            return parent::$methodName( $argsString );
        }

        \$method = {$object}->__mock()->method('$methodName');

        if ($isAbstract || \$method->isMocked()) {
            return \$method->invoke(func_get_args());
        } else {
            \$value = parent::$methodName( $argsString );
            \$method->log(func_get_args(), \$value);
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

        $propertiesDefs = $this->getPropertyDefinitions($classReflection);

        $constuctorDef = '';
        if (!$callConstructor) {
            $constuctorDef = 'public function __construct() {}';
        }

        return '
class ' . $mockClassName . ' ' . $extends . ' ' . $implements . ' {

    private $__mock;
    public static $__mockInstance;

    ' . $propertiesDefs . '

    public function __mock() {
        return $this->__mock;
    }

    ' . $constuctorDef . '

    ' . $methodDefs . '

}';
    }
}
