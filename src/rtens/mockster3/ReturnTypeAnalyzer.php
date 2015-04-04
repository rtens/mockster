<?php
namespace rtens\mockster3;

use watoki\reflect\Type;
use watoki\reflect\type\ArrayType;
use watoki\reflect\type\BooleanType;
use watoki\reflect\type\FloatType;
use watoki\reflect\type\IntegerType;
use watoki\reflect\type\MultiType;
use watoki\reflect\type\NullableType;
use watoki\reflect\type\NullType;
use watoki\reflect\type\StringType;
use watoki\reflect\TypeFactory;

class ReturnTypeAnalyzer {

    /** @var \ReflectionMethod */
    private $reflection;

    function __construct(\ReflectionMethod $reflection) {
        $this->reflection = $reflection;
    }

    public function mockValue() {
        return $this->generateMockValue();
    }

    private function generateMockValue() {
        $type = $this->getTypeFromDocComment();

        try {
            return $this->getValueFromHint($type);
        } catch (\InvalidArgumentException $e) {
            throw new \Exception("Could not generate value from return type hint.", 0, $e);
        }
    }

    private function getTypeFromDocComment() {
        $matches = array();
        $found = preg_match('/@return\s+(\S+)/', $this->reflection->getDocComment(), $matches);

        if (!$found) {
            throw new \Exception("No type hint found.");
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
        }

        throw new \InvalidArgumentException("Cannot mock value for [$type].");
    }
}