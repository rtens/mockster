<?php
namespace rtens\mockster;

class MockFactory {

    /**
     * @var array|Mock[] Instances which should always be used when mocking indexing class
     */
    private $singletons = array();

    /**
     * @var Generator
     */
    private $generator;

    private static $mockCodes = array();

    public function __construct() {
        $this->generator = new Generator($this);
    }

    /**
     * @param string $classname
     * @param null|Mock $instance
     */
    public function makeSingleton($classname, $instance = null) {
        $classname = $this->generator->normalizeClassname($classname);

        if (!$instance) {
            $instance = $this->createMock($classname);
        }
        $this->singletons[$classname] = $instance;
    }

    /**
     * Convenience method to create a mock of a class to be tested.
     *
     * All protected properties but no methods are mocked.
     *
     * @param string $classname
     * @param array|null $constructorArgs If null, parent constructor is not invoked
     * @return Mock
     */
    public function createTestUnit($classname, $constructorArgs = array()) {
        $mock = $this->createMock($classname, $constructorArgs);
        $mock->__mock()->mockProperties(Mockster::F_PROTECTED);
        $mock->__mock()->mockMethods(Mockster::F_NONE);
        return $mock;
    }

    /**
     * @param string $classname Fully qualified name of the class or interface to mock.
     * @param null|array $constructorArgs Arguments for the constructor (as list or map). If null, the constructor is not invoked.
     * @throws \InvalidArgumentException
     * @return \rtens\mockster\Mock
     */
    public function createMock($classname, $constructorArgs = null) {
        if (!is_string($classname)) {
            throw new \InvalidArgumentException('Classname must be a string.');
        }
        $classname = $this->generator->normalizeClassname($classname);

        if (array_key_exists($classname, $this->singletons)) {
            return $this->singletons[$classname];
        }

        $classReflection = new \ReflectionClass($classname);

        $mockClassName = 'Mock_' . str_replace('\\', '_', $classname);

        if ($constructorArgs === null) {
            $mockClassName .= '_NoConstructor';
        }

        if (!class_exists($mockClassName)) {
            $code = $this->generateMockCode($classname, $classReflection, $mockClassName, $constructorArgs);
            eval($code);

            self::$mockCodes[$mockClassName] = $code;
        } else {
            $code = self::$mockCodes[$mockClassName];
        }

        $mockClassRefl = new \ReflectionClass($mockClassName);

        $constructor = $classReflection->getConstructor();
        if ($constructorArgs !== null && $constructor !== null) {
            $constructorArgs = $this->generator->getMethodParameters($constructor, $constructorArgs);
            $instance = $mockClassRefl->newInstanceArgs(array_values($constructorArgs));
        } else {
            $instance = $mockClassRefl->newInstanceArgs();
        }

        $mockClassRefl->setStaticPropertyValue('__mockInstance', $instance);

        $mockProperty = $mockClassRefl->getProperty('__mock');
        $mockProperty->setAccessible(true);
        $mockProperty->setValue($instance, new Mockster($this, $classname, $instance, $constructorArgs, $code));

        return $instance;
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

    private function generateMockCode($classname, $classReflection, $mockClassName, $constructorArgs) {

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
        if ($constructorArgs === null) {
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
?>
