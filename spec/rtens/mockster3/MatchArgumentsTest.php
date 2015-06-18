<?php
namespace spec\rtens\mockster3;

use rtens\mockster3\arguments\Argument;
use rtens\mockster3\arguments\BooleanArgument;
use rtens\mockster3\arguments\ExactArgument;
use rtens\mockster3\arguments\IntegerArgument;
use rtens\mockster3\arguments\ObjectArgument;
use rtens\mockster3\arguments\RegularExpressionArgument;
use rtens\mockster3\arguments\StringArgument;
use rtens\scrut\tests\statics\StaticTestSuite;

class MatchArgumentsTest extends StaticTestSuite {

    function testInteger() {
        $this->assert(Argument::integer()->accepts(new IntegerArgument()));
        $this->assert(Argument::integer()->accepts(new ExactArgument(1)));
        $this->assert->not(Argument::integer()->accepts(new ExactArgument("1")));
    }

    function testString() {
        $this->assert(Argument::string()->accepts(new StringArgument()));
        $this->assert(Argument::string()->accepts(new ExactArgument('string')));
        $this->assert->not(Argument::string()->accepts(new ExactArgument(1)));
    }

    function testBoolean() {
        $this->assert(Argument::boolean()->accepts(new BooleanArgument()));
        $this->assert(Argument::boolean()->accepts(new ExactArgument(true)));
        $this->assert->not(Argument::boolean()->accepts(new ExactArgument('true')));
    }

    function testObject() {
        $this->assert(Argument::object(\DateTime::class)->accepts(new ExactArgument(new \DateTime())));
        $this->assert(Argument::object(\DateTime::class)->accepts(new ObjectArgument(\DateTime::class)));
        $this->assert(Argument::object(\DateTimeInterface::class)->accepts(new ObjectArgument(\DateTime::class)));
        $this->assert->not(Argument::object(\DateTime::class)->accepts(new ObjectArgument(\DateTimeInterface::class)));
    }

    function testRegularExpression() {
        $this->assert(Argument::regex('/[a-z]/')->accepts(new RegularExpressionArgument('/[a-z]/')));
        $this->assert->not(Argument::regex('/[a-z]/')->accepts(new RegularExpressionArgument('/[a-bc-z]/')));
        $this->assert(Argument::regex('/[a-z]+/')->accepts(new ExactArgument('hello world')));
        $this->assert->not(Argument::regex('/[a-z]+/')->accepts(new ExactArgument('HELLO WORLD')));
    }

    function testContaining() {
        $this->assert(Argument::contains('bar')->accepts(new ExactArgument('foo bar bas')));
        $this->assert(Argument::contains('bar')->accepts(new ExactArgument(['foo', 'bar', 'bas'])));
        $this->assert(Argument::contains('bar')->accepts(new ExactArgument($this->stack(['foo', 'bar', 'bas']))));
        $this->assert(Argument::contains('bar')->accepts(new ExactArgument($this->object(['foo' => 'bar']))));

        $this->assert->not(Argument::contains('bar')->accepts(new ExactArgument('foo')));
        $this->assert->not(Argument::contains('bar')->accepts(new IntegerArgument()));
    }

    function testCallback() {
        $callback = function (ExactArgument $argument) {
            return $argument->value() == 'foo';
        };

        $this->assert(Argument::callback($callback)->accepts(new ExactArgument('foo')));
        $this->assert->not(Argument::callback($callback)->accepts(new ExactArgument('bar')));
    }

    private function stack($items) {
        $stack = new \SplStack();
        foreach ($items as $item) {
            $stack->push($item);
        }
        return $stack;
    }

    private function object($properties) {
        return json_decode(json_encode($properties));
    }
}