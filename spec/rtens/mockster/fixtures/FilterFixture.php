<?php
namespace spec\rtens\mockster\fixtures;

use rtens\mockster\filter\Filter;
use rtens\scrut\Assert;

/**
 * @property Assert $assert <-
 */
class FilterFixture extends Fixture {

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var array|\ReflectionMethod[]
     */
    private $filterOutput = array();

    public function givenTheFilterWithTheBitMask($bitmask) {
        $this->filter = new Filter($bitmask);
    }

    public function whenTheFilterIsAppliedToTheMethodsOfClass($class) {
        $reflectionClass = new \ReflectionClass($class);
        $filter = $this->filter;
        $this->filterOutput = array_filter($reflectionClass->getMethods(), function($method) use ($filter) {
            return $filter->apply($method);
        });
    }

    public function thenTheFilterMatched_Methods($count) {
        $this->assert->size($this->filterOutput, $count);
    }

    public function thenFilterMatchesContainTheMethod($methodName) {
        $this->assert->not()->isNull($this->getFilteredMethod($methodName));
    }

    public function thenFilterMatchesDoesNotContainTheMethod($methodName) {
        $this->assert->isNull($this->getFilteredMethod($methodName));
    }

    /**
     * @param string $methodName
     * @return \ReflectionMethod|null
     */
    private function getFilteredMethod($methodName) {
        foreach ($this->filterOutput as $method) {
            if ($method->getName() == $methodName) {
                return $method;
            }
        }
        return null;
    }
}