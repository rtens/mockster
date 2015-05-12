<?php
namespace rtens\mockster;

class MockGenerator {

    public function generateMock($classname, $mockClassName, $callConstructor) {
        return $this->generateMockCode($mockClassName,
            $this->generateExtendsString($classname),
            $this->generateConstructorDefinition($callConstructor),
            $this->generateMethodDefinitions(new \ReflectionClass($classname)));
    }

    private function generateExtendsString($classname) {
        if (class_exists($classname)) {
            return ' extends ' . $classname;
        } else if (interface_exists($classname)) {
            return 'implements ' . $classname;
        } else {
            return '';
        }
    }

    private function generateConstructorDefinition($callConstructor) {
        if (!$callConstructor) {
            return 'public function __construct() {}';
        } else {
            return '';
        }
    }

    private function generateMethodDefinitions(\ReflectionClass $classReflection) {
        $methods = '';

        foreach ($classReflection->getMethods() as $method) {
            if ($this->isMockable($method)) {
                $methods .= $this->generateMethodDefinition($method);
            }
        }

        return $methods;
    }

    private function isMockable(\ReflectionMethod $method) {
        return !($method->isStatic()
            || $method->isPrivate()
            || $method->isFinal()
            || $method->isConstructor());
    }

    private function generateMethodDefinition(\ReflectionMethod $method) {
        return $this->generateMethodCode(
            $method->getDocComment(),
            $method->getName(),
            implode(', ', $this->generateMethodParameters($method)),
            implode(', ', $this->generateMethodArguments($method)),
            $method->isAbstract() ? 'true' : 'false');
    }

    private function generateMethodArguments(\ReflectionMethod $method) {
        $args = array();
        foreach ($method->getParameters() as $param) {
            $args[] = '$' . $param->getName();
        }
        return $args;
    }

    private function generateMethodParameters(\ReflectionMethod $method) {
        $params = array();

        $parameters = $method->getParameters();
        foreach ($parameters as $i => $param) {
            $typeHint = $this->generateTypeHint($param);
            $defaultValue = $this->generateDefaultValue($param);

            if ($i + 1 == count($parameters) && $method->isVariadic()) {
                $typeHint .= ' ...';
                $defaultValue = '';
            }

            $params[] = $typeHint
                . $this->generateReferenceToken($param)
                . '$' . $param->getName()
                . $defaultValue;
        }
        return $params;
    }

    private function generateTypeHint(\ReflectionParameter $param) {
        if ($param->isArray()) {
            return 'array ';
        } else if ($param->isCallable()) {
            return 'callable ';
        } else if ($param->getClass()) {
            return $param->getClass()->getName() . ' ';
        } else {
            return '';
        }
    }

    private function generateDefaultValue(\ReflectionParameter $param) {
        if ($param->isDefaultValueAvailable()) {
            $value = $param->getDefaultValue();
            return ' = ' . var_export($value, true);
        } else if ($param->isOptional()) {
            return ' = null';
        } else {
            return '';
        }
    }

    private function generateReferenceToken(\ReflectionParameter $param) {
        if ($param->isPassedByReference()) {
            return '&';
        } else {
            return '';
        }
    }

    private function generateMockCode($mockClassName, $extends, $constuctorDef, $methodDefs) {
        return '
class ' . $mockClassName . ' ' . $extends . ' {

    public $__stubs;

    ' . $constuctorDef . '

    ' . $methodDefs . '

}';
    }

    private function generateMethodCode($docComment, $methodName, $paramsString, $argsString) {
        return "

    $docComment
    public function $methodName ( $paramsString ) {
        \$stub = \$this->__stubs->find('$methodName', func_get_args());

        try {
            if (!\$stub->isStubbed()) {
                \$return = parent::$methodName($argsString);
            } else {
                \$return = \$stub->invoke(func_get_args());
            }
        } catch (\\Exception \$e) {
            \$stub->record(func_get_args(), null, \$e);
            throw \$e;
        }

        \$stub->record(func_get_args(), \$return);
        return \$return;
    }";
    }
}