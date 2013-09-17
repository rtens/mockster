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
                return $this->factory->getInstance($className);
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

    private function explodeMultipleHints($hint) {
        if (strpos($hint, '|') !== false) {
            return explode('|', $hint);
        } else {
            return array($hint);
        }
    }

}
