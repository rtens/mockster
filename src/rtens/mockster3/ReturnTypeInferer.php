<?php
namespace rtens\mockster3;

use watoki\factory\Factory;
use watoki\reflect\Type;
use watoki\reflect\type\ArrayType;
use watoki\reflect\type\BooleanType;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\FloatType;
use watoki\reflect\type\IntegerType;
use watoki\reflect\type\MultiType;
use watoki\reflect\type\NullableType;
use watoki\reflect\type\NullType;
use watoki\reflect\type\StringType;
use watoki\reflect\type\UnknownType;
use watoki\reflect\TypeFactory;

class ReturnTypeInferer {

    /** @var \ReflectionMethod */
    private $reflection;

    /** @var Factory */
    private $factory;

    function __construct(\ReflectionMethod $reflection, Factory $factory) {
        $this->reflection = $reflection;
        $this->factory = $factory;
    }

    public function mockValue() {
        return $this->getValueFromHint($this->getType());
    }

    /**
     * @return Type
     */
    public function getType() {
        $matches = array();
        $found = preg_match('/@return\s+(\S+)/', $this->reflection->getDocComment(), $matches);

        if (!$found) {
            return new UnknownType('');
        }

        $type = new TypeFactory($this->reflection->getDeclaringClass());
        return $type->fromTypeHints(explode("|", $matches[1]));
    }

    private function getValueFromHint(Type $type) {
        if ($type instanceof IntegerType) {
            return 0;
        } else if ($type instanceof FloatType) {
            return 0.0;
        } else if ($type instanceof BooleanType) {
            return false;
        } else if ($type instanceof StringType) {
            return '';
        } else if ($type instanceof ArrayType) {
            return array();
        } else if ($type instanceof NullType
            || $type instanceof NullableType
        ) {
            return null;
        } else if ($type instanceof MultiType) {
            return $this->getValueFromHint($type->getTypes()[0]);
        } else if ($type instanceof ClassType) {
            return $this->factory->getInstance($type->getClass(), null);
        }

        throw new \InvalidArgumentException("Cannot mock value for [$type].");
    }
}