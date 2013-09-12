<?php
namespace rtens\mockster;

/**
 * A helper class generating mocking parameters and creating default instances.
 */
class Generator {

    /**
     * @var MockFactory
     */
    private $factory;

    /**
     * @param MockFactory $factory The factory used for creating mocks
     */
    public function __construct(MockFactory $factory) {
        $this->factory = $factory;
    }

    /**
     * Creates values for arguments (if not passed) of a method.
     *
     * Creates mocks of classes and default values for primitives.
     *
     * @param \ReflectionMethod $method
     * @param array $arguments Passed arguments, indexed either by name or index (name has priority)
     * @return array
     */
    public function getMethodParameters(\ReflectionMethod $method, array $arguments) {
        $argsInOrder = array();

        foreach ($method->getParameters() as $param) {
            if (array_key_exists($param->getName(), $arguments)) {
                $arg = $arguments[$param->getName()];
            } else if (array_key_exists($param->getPosition(), $arguments)) {
                $arg = $arguments[$param->getPosition()];
            } else if ($param->isArray()) {
                $arg = array();
            } elseif ($param->isOptional()) {
                $arg = $param->getDefaultValue();
            } else if ($param->getClass()) {
                $arg = $this->factory->createMock($param->getClass()->getName());
            } else {
                $arg = $this->getInstanceFromDocCommentParam($method, $param);
            }
            $argsInOrder[$param->getName()] = $arg;
        }
        return $argsInOrder;
    }

    /**
     * @param \ReflectionMethod $method
     * @param \ReflectionParameter $param
     * @return array|bool|float|int|null|Mock|string
     */
    private function getInstanceFromDocCommentParam(\ReflectionMethod $method, \ReflectionParameter $param) {
        $arg = null;
        $parser = new AnnotationParser($method->getDocComment());
        foreach ($parser->findAll('param') as $annotation) {
            if (strpos($annotation, ' $') === false) {
                continue;
            }

            list($hint, $variable) = explode(' $', $annotation);
            if ($variable != $param->getName()) {
                continue;
            }

            $arg = $this->getInstanceFromHint($hint, $method->getDeclaringClass());
        }
        return $arg;
    }

    /**
     * Returns a mock or default value based on a type hint which may contain several classes spearated by |
     *
     * e.g.
     * string[]|array => array()
     * null|string => null
     * string|null => ''
     *
     * @param string $hint
     * @param \ReflectionClass $class
     * @throws \InvalidArgumentException
     * @return Mock|null|array|bool|float|int|string
     */
    public function getInstanceFromHint($hint, \ReflectionClass $class) {
        if (!$hint) {
            return null;
        }

        foreach ($this->explodeMultipleHints($hint) as $typeHint) {
            $resolver = new ClassResolver($class);
            $className = $resolver->resolve($typeHint);
            if ($className) {
                return $this->factory->createMock($className);
            }

            try {
                return $this->getPrimitiveFromHint($typeHint);
            } catch (\InvalidArgumentException $e) {
            }
        }

        throw new \InvalidArgumentException('Could not create mock. No class in type hint exists [' . $hint . ']');
    }

    /**
     * Creates a mocked object or default value for given class or primitive.
     *
     * @param string $type
     * @return array|bool|float|int|Mock|null|string
     * @throws \InvalidArgumentException
     */
    private function getPrimitiveFromHint($type) {
        switch (strtolower($type)) {
            case 'array':
                return array();
            case 'int':
            case 'integer':
                return 0;
            case 'float':
                return 0.0;
            case 'bool':
            case 'boolean':
                return false;
            case 'string':
                return '';
            case 'null':
            case 'mixed':
            case 'object':
            case 'callable':
            case 'void':
            case 'closure':
                return null;
        }

        throw new \InvalidArgumentException("Not a primitive type [$type].");
    }

    /**
     * Removes a leading backslash from the classname
     *
     * @param string $classname
     * @return string Classname without leading backslash
     */
    public function normalizeClassname($classname) {
        if (substr($classname, 0, 1) == '\\') {
            return substr($classname, 1);
        }
        return $classname;
    }

    private function explodeMultipleHints($hint) {
        if (strpos($hint, '|') !== false) {
            return explode('|', $hint);
        } else {
            return array($hint);
        }
    }

}
