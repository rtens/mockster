<?php
namespace mockster;

class MockFactory {

    /**
     * @var array|Mock[] Instances which should always be used when mocking indexing class
     */
    private static $singletons = array();

    /**
     * @param string $classname
     * @param null|Mock $instance
     */
    public function makeSingleton($classname, $instance = null) {
        $classname = $this->normalizeClassname($classname);

        if (!$instance) {
            $instance = $this->createMock($classname);
        }
        self::$singletons[$classname] = $instance;
    }

    /**
     * @param string $classname Fully qualified name of the class or interface to mock.
     * @param null|array $constructorArgs Arguments for the constructor (as list or map). If null, the constructor is not invoked.
     * @param boolean $mockDependencies
     * @return \mockster\Mock
     */
    public function createMock($classname, $constructorArgs = array(), $mockDependencies = true) {
        $classname = $this->normalizeClassname($classname);

        if (array_key_exists($classname, self::$singletons)) {
            return self::$singletons[$classname];
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

    private $__mockster;
    public static $__mockInstance;

    ' . $propertiesDefs . '

    public function __mock() {
        if ($this->__mockster === null) {
            $this->__mockster = new ' . __NAMESPACE__ . '\\Mockster(\'' . $classname . '\', $this);
        }
        return $this->__mockster;
    }

    ' . $constuctorDef . '

    ' . $methodDefs . '

}';

        eval($code);

        $mockClassRefl = new \ReflectionClass($mockClassName);

        $constructor = $classReflection->getConstructor();
        if ($constructorArgs !== null && $constructor !== null) {

            foreach ($constructor->getParameters() as $param) {
                if (array_key_exists($param->getPosition(), $constructorArgs)
                        || array_key_exists($param->getName(), $constructorArgs)) {
                    continue;
                }

                if ($param->isArray()) {
                    $arg = array();
                } else if ($param->getClass()) {
                    $arg = $this->createMock($param->getClass()->getName(), null, false);
                } elseif ($param->isOptional()) {
                    $arg = $param->getDefaultValue();
                } else  {
                    $arg = null;
                }
                $constructorArgs[$param->getName()] = $arg;
            }

            $instance = $mockClassRefl->newInstanceArgs(array_values($constructorArgs));
        } else {
            $instance = $mockClassRefl->newInstanceArgs();
        }

        $mockClassRefl->setStaticPropertyValue('__mockInstance', $instance);

        /** @var $mockster Mockster */
        /** @var $instance Mock */
        $mockster = $instance->__mock();

        $mockster->setConstructorArguments($constructorArgs);
        $mockster->setCode($code);

        if ($mockDependencies) {
            foreach ($classReflection->getProperties() as $property) {

                $matches = array();
                if (preg_match('/@var (\S*)/', $property->getDocComment(), $matches) == 0) {
                    continue;
                }
                $classHint = $matches[1];
                if (strpos($classHint, '|') !== false) {
                    $alternatives = explode('|', $classHint);
                    $classHint = $alternatives[0];
                }

                if (!class_exists($classHint)) {
                    continue;
                }

                $property->setAccessible(true);
                $property->setValue($instance, $this->createMock($classHint, null, false));

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

    /**
     * Removes a leading backslash from the classname
     *
     * @param string $classname
     * @return string Classname without leading backslash
     */
    private function normalizeClassname($classname) {
        if (substr($classname, 0, 1) == '\\') {
            return substr($classname, 1);
        }
        return $classname;
    }

}
?>
