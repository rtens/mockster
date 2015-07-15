<?php
namespace rtens\mockster;

use watoki\factory\Factory;
use watoki\reflect\Type;
use watoki\reflect\type\ArrayType;
use watoki\reflect\type\BooleanType;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\FloatType;
use watoki\reflect\type\IntegerType;
use watoki\reflect\type\MultiType;
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
        return $this->getValueForType($this->getType());
    }

    /**
     * @return Type|UnknownType
     */
    public function getType() {
        return $this->getAnnotationType('return');
    }

    /**
     * @return Type|UnknownType
     */
    public function getExceptionType() {
        return $this->getAnnotationType('throws');
    }

    private function getAnnotationType($annotation) {
        $matches = array();
        $found = preg_match('/@' . $annotation . '\s+(\S+)/', $this->reflection->getDocComment(), $matches);

        if (!$found) {
            return new UnknownType();
        }

        $types = new TypeFactory();
        return $types->fromTypeHints(explode("|", $matches[1]), $this->reflection->getDeclaringClass());
    }

    /**
     * @param Type $type
     * @return int|float|bool|string|array|object|null
     */
    private function getValueForType(Type $type) {
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
        } else if ($type instanceof MultiType) {
            return $this->getValueForType($type->getTypes()[0]);
        } else if ($type instanceof ClassType) {
            return (new Mockster($type->getClass(), $this->factory))->__mock();
        } else {
            return null;
        }
    }
}