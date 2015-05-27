<?php
namespace spec\rtens\mockster3;

use rtens\mockster3\arguments\Argument;
use rtens\mockster3\arguments\BooleanArgument;
use rtens\mockster3\arguments\ExactArgument;
use rtens\mockster3\arguments\IntegerArgument;
use rtens\mockster3\arguments\ObjectArgument;
use rtens\mockster3\arguments\RegularExpressionArgument;
use rtens\mockster3\arguments\StringArgument;
use watoki\scrut\Specification;

class MatchArgumentsTest extends Specification {

    function testInteger() {
        $this->assertTrue(Argument::integer()->accepts(new IntegerArgument()));
        $this->assertTrue(Argument::integer()->accepts(new ExactArgument(1)));
        $this->assertFalse(Argument::integer()->accepts(new ExactArgument("1")));
    }

    function testString() {
        $this->assertTrue(Argument::string()->accepts(new StringArgument()));
        $this->assertTrue(Argument::string()->accepts(new ExactArgument('string')));
        $this->assertFalse(Argument::string()->accepts(new ExactArgument(1)));
    }

    function testBoolean() {
        $this->assertTrue(Argument::boolean()->accepts(new BooleanArgument()));
        $this->assertTrue(Argument::boolean()->accepts(new ExactArgument(true)));
        $this->assertFalse(Argument::boolean()->accepts(new ExactArgument('true')));
    }

    function testObject() {
        $this->assertTrue(Argument::object('DateTime')->accepts(new ExactArgument(new \DateTime())));
        $this->assertTrue(Argument::object('DateTime')->accepts(new ObjectArgument('DateTime')));
        $this->assertTrue(Argument::object('DateTimeInterface')->accepts(new ObjectArgument('DateTime')));
        $this->assertFalse(Argument::object('DateTime')->accepts(new ObjectArgument('DateTimeInterface')));
    }

    function testRegularExpression() {
        $this->assertTrue(Argument::regex('/[a-z]/')->accepts(new RegularExpressionArgument('/[a-z]/')));
        $this->assertFalse(Argument::regex('/[a-z]/')->accepts(new RegularExpressionArgument('/[a-bc-z]/')));
        $this->assertTrue(Argument::regex('/[a-z]+/')->accepts(new ExactArgument('hello world')));
        $this->assertFalse(Argument::regex('/[a-z]+/')->accepts(new ExactArgument('HELLO WORLD')));
    }

    function testCallback() {
        $callback = function (ExactArgument $argument) {
            return $argument->value() == 'foo';
        };

        $this->assertTrue(Argument::callback($callback)->accepts(new ExactArgument('foo')));
        $this->assertFalse(Argument::callback($callback)->accepts(new ExactArgument('bar')));
    }
}