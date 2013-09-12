<?php
namespace rtens\mockster;
 
class ClassResolver {

    static private $cache = array();

    private $context;

    public function __construct(\ReflectionClass $context) {
        $this->context = $context;
    }

    public function resolve($class) {
        if ($this->exists($class)) {
            return $class;
        }

        $prependedClass = $this->prependWithCurrentNamespace($class);
        if ($this->exists($prependedClass)) {
            return $prependedClass;
        }

        return $this->findAliasedClass($class);
    }

    public function exists($class) {
        return class_exists($class) || interface_exists($class);
    }

    private function prependWithCurrentNamespace($class) {
        return $this->context->getNamespaceName() . '\\' . $class;
    }

    private function findAliasedClass($class) {
        $stmts = $this->parse($class);

        foreach ($stmts as $stmt) {
            if ($stmt instanceof \PHPParser_Node_Stmt_Namespace) {
                $stmts = $stmt->stmts;
                break;
            }
        }

        foreach ($stmts as $stmt) {
            if ($stmt instanceof \PHPParser_Node_Stmt_Use) {
                foreach ($stmt->uses as $use) {
                    if ($use instanceof \PHPParser_Node_Stmt_UseUse && $use->alias == $class) {
                        return $use->name->toString();
                    }
                }
            }
        }

        return null;
    }

    private function parse($class) {
        $contextName = $this->context->getName();
        if (!array_key_exists($contextName, self::$cache)) {
            try {
                $parser = new \PHPParser_Parser(new \PHPParser_Lexer());
                self::$cache[$contextName] = $parser->parse(file_get_contents($this->context->getFileName()));
            } catch (\PHPParser_Error $e) {
                throw new \Exception("Error while parsing [$class]: " . $e->getMessage());
            }
        }

        return self::$cache[$contextName];
    }

}
