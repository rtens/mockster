<?php
namespace mockster;

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
                $arg = $this->getInstance('array');
            } elseif ($param->isOptional()) {
                $arg = $param->getDefaultValue();
            } else if ($param->getClass()) {
                $arg = $this->getInstance($param->getClass()->getName());
            } else {
                $matches = array();
                if (preg_match('/@param (\S+) \$' . $param->getName() . '/', $method->getDocComment(), $matches)) {
                    $arg = $this->getInstanceFromHint($matches[1]);
                } else {
                    $arg = null;
                }
            }
            $argsInOrder[$param->getName()] = $arg;
        }
        return $argsInOrder;
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
     * @return array|bool|float|int|Mock|null|string
     * @throws \InvalidArgumentException
     */
    public function getInstanceFromHint($hint) {
        if (strpos($hint, '|') !== false) {
            $typeHints = explode('|', $hint);
        } else {
            $typeHints = array($hint);
        }

        foreach ($typeHints as $typeHint) {
            if ($typeHint == null) {
                return null;
            }

            try {
                return $this->getInstance($typeHint);
            } catch (\InvalidArgumentException $e) {
            }
        }

        throw new \InvalidArgumentException('Could not create mock. No class in type hint exists [' . $hint . ']');
    }

    /**
     * Creates a mocked object or default value for given class or primitive.
     *
     * @param string $class
     * @return array|bool|float|int|Mock|null|string
     * @throws \InvalidArgumentException
     */
    public function getInstance($class) {
        $class = $this->normalizeClassname($class);

        if (class_exists($class) || interface_exists($class)) {
            return $this->factory->createMock($class, null, false);
        }

        switch (strtolower($class)) {
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
            case 'closure':
                return null;
        }

        throw new \InvalidArgumentException('Could not create mock. Class does not exist [' . $class . ']');
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

}
