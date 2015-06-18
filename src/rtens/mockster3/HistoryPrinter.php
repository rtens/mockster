<?php
namespace rtens\mockster3;

use rtens\mockster3\arguments\Argument;
use watoki\reflect\ValuePrinter;

class HistoryPrinter {

    /**
     * @param Stub $stub
     * @return string
     */
    public function printStub(Stub $stub) {
        $class = $stub->className();
        $method = $stub->methodName();
        $calls = $stub->has()->calls();

        if (!$calls) {
            return "No calls recorded for [{$class}::{$method}()]";
        }

        return "History of [{$class}::{$method}()]\n  " . $this->printCalls($method, $calls);
    }

    public function printAll(Mockster $mockster) {
        $class = (new \ReflectionClass($mockster->mock()))->getParentClass();

        $all = [];
        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $arguments = array_map(function () {
                return Argument::any();
            }, $method->getParameters());

            $calls = $mockster->__call($method->getName(), $arguments)->has()->calls();
            if ($calls) {
                $all[] = $this->printCalls($method->getName(), $calls);
            }
        }

        return "History of [{$class->getName()}]\n  " . implode("\n  ", $all);
    }

    /**
     * @param string $method
     * @param Call[] $allCalls
     * @return string The printed history
     */
    public function printCalls($method, $allCalls) {
        $calls = [];
        foreach ($allCalls as $call) {
            $calls[] = $this->printCall($method, $call);
        }
        return implode("\n  ", $calls);
    }

    private function printCall($method, Call $call) {
        return "{$method}("
        . implode(', ', $this->printArguments($call)) . ') '
        . $this->printResult($call);
    }

    private function printArguments(Call $call) {
        $arguments = [];
        foreach ($call->arguments() as $i => $argument) {
            if (!is_numeric($i)) {
                continue;
            }
            $arguments[] = $this->toString($argument);
        }
        return $arguments;
    }

    private function printResult(Call $call) {
        if ($call->thrown()) {
            $result = '!! ' . $this->toString($call->thrown());
            return $result;
        } else {
            $result = '-> ' . $this->toString($call->returned());
            return $result;
        }
    }

    private function toString($value) {
        return ValuePrinter::serialize($value);
    }
}