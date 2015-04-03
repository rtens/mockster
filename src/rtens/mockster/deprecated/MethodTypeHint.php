<?php
namespace rtens\mockster\deprecated;

use watoki\reflect\ClassResolver;

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
     * @var callable|array|bool|float|int|string
     */
    private $defaultValue;

    /**
     * @var MockFactory
     */
    private $factory;

    /**
     * @param \rtens\mockster\deprecated\MockFactory $factory
     * @param \ReflectionMethod $reflection
     */
    public function __construct(MockFactory $factory, \ReflectionMethod $reflection) {
        $this->factory = $factory;
        $this->reflection = $reflection;
    }

    /**
     * Returns a list of all types which have been declared as return type of the method
     *
     * @return array
     */
    public function getTypeHints() {
        return $this->getTypeHintsFromDocComment();
    }

    /**
     * Evaluates if the given value matches at least one of the given method type hints
     *
     * @param mixed $value
     * @return bool
     */
    public function matchesTypeHint($value) {
        $types = $this->getTypeHintsFromDocComment();

        if (!$types) {
            // if no types are hinted, we assume that every return value matches
            return true;
        }

        foreach ($types as $type) {
            if ($this->isOfType($type, $value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns a mock or scalar default value based on a type hint which may contain several classes separated by |
     *
     * @return Mock|null|array|bool|float|int|string
     */
    public function getDefaultValue() {
        if ($this->defaultValue === null) {
            $this->defaultValue = $this->generateDefaultValue();
        }

        $v = $this->defaultValue;
        return is_callable($v) ? $v() : $v;
    }

    /**
     * Iterates over all type hints in the doc comment and tries to find a matching default value
     * If the returned value is a class instance or null a generator callback is returned instead
     *
     * @return callable|array|bool|float|int|string
     */
    private function generateDefaultValue() {
        $types = $this->getTypeHintsFromDocComment();

        foreach ($types as $type) {
            try {
                $value = $this->getValueFromHint($type);
                if ($value !== null) {
                    return $value;
                }
            } catch (\InvalidArgumentException $e) {
            }
        }

        return function () {
            return null;
        };
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

        $types = $found ? $this->explodeMultipleHints($matches[1]) : array();
        $resolver = new ClassResolver($this->reflection->getDeclaringClass());
        $factory = $this->factory;

        $this->types = array_map(function ($type) use ($factory, $resolver) {
            if (substr($type, -2) === '[]') {
                return 'Traversable';
            }
            $className = $resolver->resolve($type);
            return $className ? : $type;
        }, $types);

        return $this->types;
    }

    /**
     * @param string $type
     * @param mixed $value
     * @return bool
     */
    private function isOfType($type, $value) {
        switch (strtolower($type)) {
            case 'array':
                return is_array($value);
            case 'int':
            case 'integer':
                return is_int($value);
            case 'float':
                return is_float($value);
            case 'bool':
            case 'boolean':
                return is_bool($value);
            case 'string':
                return is_string($value);
            case 'object':
                return is_object($value);
            case 'null':
                return is_null($value);
            case 'mixed':
                return true;
            case 'callable':
            case 'closure':
                return is_callable($value);
            case 'void':
                return is_null($value);
            case 'traversable':
                return is_array($value) || $value instanceof \Traversable;
        }
        return $value instanceof $type;
    }

    /**
     * @param string $type
     * @return array|bool|float|int|null|string
     * @throws \InvalidArgumentException
     */
    private function getValueFromHint($type) {
        switch (strtolower($type)) {
            case 'array':
            case 'traversable':
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

        $resolver = new ClassResolver($this->reflection->getDeclaringClass());
        $className = $resolver->resolve($type);

        if ($className) {
            $factory = $this->factory;
            return function () use ($factory, $className) {
                return $factory->getInstance($className);
            };
        }

        throw new \InvalidArgumentException("Cannot resolve value for [$type].");
    }

    /**
     * @param string $hint
     * @return array
     */
    private function explodeMultipleHints($hint) {
        if (strpos($hint, '|') !== false) {
            return explode('|', $hint);
        } else {
            return array($hint);
        }
    }
}