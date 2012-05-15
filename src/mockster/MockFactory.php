<?php
namespace mockster;

class MockFactory {

    /**
     * @var array|Mock[] Instances which should always be used when mocking indexing class
     */
    private $singletons = array();

    /**
     * @var Generator
     */
    private $generator;

    /**
     * @var string
     */
    private $propertyAnnotation;

    public function __construct() {
        $this->generator = new Generator($this);
    }

    /**
     * If set, only properties having the given annotation will be mocked.
     *
     * e.g. 'inject' only mocks properties with an '@inject' annotation
     *
     * @param string|null $annotation
     */
    public function onlyMockAnnotatedProperties($annotation) {
        $this->propertyAnnotation = $annotation;
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
     * Convenience method to create a mock of a class to be testeed.
     *
     * @param string $classname
     * @param array $constructorArgs
     * @return Mock
     */
    public function createTestUnit($classname, $constructorArgs = array()) {
        $mock = $this->createMock($classname, $constructorArgs, true);
        $mock->__mock()->dontMockPublicMethods();
        return $mock;
    }

    /**
     * @param string $classname Fully qualified name of the class or interface to mock.
     * @param null|array $constructorArgs Arguments for the constructor (as list or map). If null, the constructor is not invoked.
     * @param boolean $mockDependencies
     * @throws \InvalidArgumentException
     * @return \mockster\Mock
     */
    public function createMock($classname, $constructorArgs = null, $mockDependencies = false) {
        if (!is_string($classname)) {
            throw new \InvalidArgumentException('Classname must be a string.');
        }
        $classname = $this->generator->normalizeClassname($classname);

        if (array_key_exists($classname, $this->singletons)) {
            return $this->singletons[$classname];
        }

        $implements = '';
        $extends = '';
        if (interface_exists($classname)) {
            $implements = ' implements ' . $classname;
        } else if (class_exists($classname)) {
            $extends = ' extends ' . $classname;
        }

        $classReflection = new \ReflectionClass($classname);


        $constuctorDef = '';
        if ($constructorArgs === null) {
            $constuctorDef = 'public function __construct() {}';
        }

        $methodDefs = $this->getMethodDefinitions($classReflection);

        $propertiesDefs = $this->getPropertyDefinitions($classReflection);

        $mockClassName = 'Mock_' . $classReflection->getShortName() . '_' . substr(md5(microtime()), 0, 8);

        $code = '
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

        eval($code);

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

        if ($mockDependencies) {
            foreach ($classReflection->getProperties() as $property) {
                if ($this->propertyAnnotation &&
                        preg_match('/@' . $this->propertyAnnotation . '/', $property->getDocComment(), $matches) == 0) {
                    continue;
                }

                $matches = array();
                if (preg_match('/@var (\S+)/', $property->getDocComment(), $matches) == 0) {
                    continue;
                }
                $property->setAccessible(true);

                $dependencyMock = $this->generator->getInstanceFromHint($matches[1]);
                $property->setValue($instance, $dependencyMock);
            }
        }

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

            $static = $method->isStatic() ? 'static' : '';

            $object = $method->isStatic() ? 'self::$__mockInstance' : '$this';

            $methods .= "

    public $static function $methodName ( $paramsString ) {
        \$method = {$object}->__mock()->method('$methodName');

        if (\$method->isMocked()) {
            return \$method->invoke(func_get_args());
        } else {
            \$method->log(func_get_args());
            return parent::$methodName( $argsString );
        }
    }";
        }

        return $methods;
    }

}
?>
