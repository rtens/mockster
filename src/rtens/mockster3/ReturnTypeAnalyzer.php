<?php
namespace rtens\mockster3;

class ReturnTypeAnalyzer {

    /** @var \ReflectionMethod */
    private $reflection;

    function __construct(\ReflectionMethod $reflection) {
        $this->reflection = $reflection;
    }

    public function mockValue() {
        return $this->generateDefaultValue();
    }

    private function generateDefaultValue() {
        $types = $this->getTypeHintsFromDocComment();

        foreach ($types as $type) {
            try {
                return $this->getValueFromHint($type);
            } catch (\Exception $e) {
            }
        }

        throw new \Exception("Could not generate value from " . json_encode($types));
    }

    private function getTypeHintsFromDocComment() {
        $matches = array();
        $found = preg_match('/@return\s+(\S+)/', $this->reflection->getDocComment(), $matches);

        return $found ? $this->explodeMultipleHints($matches[1]) : array();
    }

    private function explodeMultipleHints($hint) {
        if (strpos($hint, '|') !== false) {
            return explode('|', $hint);
        } else {
            return array($hint);
        }
    }

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

        throw new \InvalidArgumentException("Cannot resolve value for [$type].");
    }
}