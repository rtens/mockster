<?php
namespace rtens\mockster;

use watoki\factory\ClassResolver;

class MethodTypeHint {

    /**
     * @var \ReflectionMethod
     */
    private $reflection;

    /**
     * Possible return types parsed from doc comment
     *
     * @var string
     */
    private $types;

    /**
     * @var callable
     */
    private $defaultValueCallback;

    /**
     * @var MockFactory
     */
    private $factory;

    /**
     * @param \rtens\mockster\MockFactory $factory
     * @param \ReflectionMethod $reflection
     */
    public function __construct(MockFactory $factory, \ReflectionMethod $reflection) {
        $this->factory = $factory;
        $this->reflection = $reflection;
    }

    /**
     * Returns a mock or scalar default value based on a type hint which may contain several classes separated by |
     *
     * @return Mock|null|array|bool|float|int|string
     */
    public function getDefaultValue() {
        if ($this->defaultValueCallback === null) {
            $this->defaultValueCallback = $this->getDefaultValueCallback();
        }

        $fn = $this->defaultValueCallback;
        return $fn();
    }

    /**
     * Iterates over all type hints in the doc comment and creates a callback which will return the default value
     *
     * @return callable
     */
    private function getDefaultValueCallback() {
        $types = $this->getTypeHintsFromDocComment();
        $class = $this->reflection->getDeclaringClass();

        foreach ($types as $type) {
            $resolver = new ClassResolver($class);
            $className = $resolver->resolve($type);
            if ($className) {
                $factory = $this->factory;
                return function() use ($factory, $className) {
                    return $factory->getInstance($className);
                };
            }

            try {
                $value = $this->getPrimitiveFromHint($type);
                return function() use ($value) {
                    return $value;
                };
            } catch (\InvalidArgumentException $e) {}
        }

        return function() {};
    }

    /**
     * @return array
     */
    private function getTypeHintsFromDocComment() {
        if ($this->types !== null) {
            return $this->types;
        }

        $matches = array();
        $found = preg_match('/@return\s+(\S+)/', $this->reflection->getDocComment(), $matches);

        $this->types = $found ? $this->explodeMultipleHints($matches[1]) : array();
        return $this->types;
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