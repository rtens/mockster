<?php
namespace spec\rtens\mockster\fixtures;

use rtens\mockster\filter\Filter;

class FilterFixture {

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var array|\ReflectionMethod[]
     */
    private $filterOutput = array();

    /**
     * @param \PHPUnit_Framework_Assert|Specification $spec
     */
    function __construct($spec) {
        $this->spec = $spec;
    }

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
        $this->spec->assertEquals($count, count($this->filterOutput));
    }

    public function thenFilterMatchesContainTheMethod($methodName) {
        $this->spec->assertNotNull($this->getFilteredMethod($methodName));
    }

    public function thenFilterMatchesDoesNotContainTheMethod($methodName) {
        $this->spec->assertNull($this->getFilteredMethod($methodName));
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