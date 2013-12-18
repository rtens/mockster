<?php
namespace spec\rtens\mockster;

use watoki\scrut\Specification;

/**
 * @property MockFactoryFixture fixture <-
 */
class RecordHistoryTest extends Specification {

    public function testMethodStubRecordsCalls() {
        $this->fixture->givenTheClassDefinition('
            class RecordCalls {
                public function myFunction($arg = null) {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('RecordCalls');

        $this->fixture->whenIInvoke('myFunction');
        $this->fixture->whenIInvoke_WithTheArgument('myFunction', 'a');

        $this->fixture->thenTheCallCountOf_ShouldBe('myFunction', 2);
        $this->fixture->thenTheArgumentsOfCallIndex_OfMethod_ShouldBe(0, 'myFunction', array());
        $this->fixture->thenTheArgumentsOfCallIndex_OfMethod_ShouldBe(-2, 'myFunction', array());
        $this->fixture->thenTheArgumentsOfCallIndex_OfMethod_ShouldBe(1, 'myFunction', array('arg' => 'a'));
        $this->fixture->thenTheArgument_OfCallIndex_OfMethod_ShouldBe(0, 1, 'myFunction', 'a');
        $this->fixture->thenTheArgument_OfCallIndex_OfMethod_ShouldBe(0, -1, 'myFunction', 'a');
        $this->fixture->thenTheArgument_OfCallIndex_OfMethod_ShouldBe('arg', 1, 'myFunction', 'a');
    }

    public function testFalseAndNullArgumentsAreRecorded() {
        $this->fixture->givenTheClassDefinition('
            class RecordFalseAndNull {
                public function myFunction($arg = null, $default = true) {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('RecordFalseAndNull');

        $this->fixture->whenIInvoke_WithTheArgument('myFunction', false);
        $this->fixture->whenIInvoke_WithTheArgument('myFunction', null);

        $this->fixture->thenTheCalledArgumentsOf_ShouldBe('myFunction', array(
            array('arg' => false),
            array('arg' => null)
        ));
    }

    public function testWasCalledWith() {
        $this->fixture->givenTheClassDefinition('
            class MatchCalls {
                public function myFunction($arg1, $arg2) {}
            }
        ');
        $this->fixture->whenICreateTheMockOf('MatchCalls');

        $this->fixture->whenIInvoke_WithTheArgument_And('myFunction', 'a', 'b');

        $this->fixture->thenTheMethod_WasCalledWith('myFunction', array('a', 'b'));
        $this->fixture->thenTheMethod_WasCalledWith('myFunction', array('arg1' => 'a', 'arg2' => 'b'));
        $this->fixture->thenTheMethod_WasCalledWith('myFunction', array('arg2' => 'b'));
        $this->fixture->thenTheMethod_WasCalledWith('myFunction', array('arg1' => 'a'));
    }

    public function testLogNotMockedMethodCalls() {
        $this->fixture->givenTheClassDefinition('
            class tLogNotMocked {
                public function myFunction($arg1, $arg2) {
                    return "x";
                }
            }
        ');
        $this->fixture->whenICreateTheMockOf('tLogNotMocked');

        $this->fixture->whenIUnMockTheMethod('myFunction');
        $this->fixture->whenIInvoke_WithTheArgument_And('myFunction', 'a', 'b');

        $this->fixture->thenTheHistoryOf_ShouldBe('myFunction', "myFunction (1)\n  (a, b) -> x\n");
    }

    public function testCompleteHistory() {
        $this->fixture->givenTheClassDefinition('
            class CompleteHistory {
                public function one($one1, $one2) {}
                public function two($two1) {}
                public function three() {
                    return "returnThree";
                }
            }
        ');
        $this->fixture->whenICreateTheMockOf('CompleteHistory');

        $this->fixture->whenIConfigureTheMethod_ToCallAClosure('one');
        $this->fixture->whenIConfigureTheMethod_ToReturn_WhenCalledWithTheArgument('two', 'returnTwoD', 'd');
        $this->fixture->whenIConfigureTheMethod_ToReturn_WhenCalledWithTheArgument('two', 'returnTwoC', 'c');
        $this->fixture->whenIUnMockTheMethod('three');

        $this->fixture->whenIInvoke_WithTheArgument_And('one', 'a', 'b');
        $this->fixture->whenIInvoke_WithTheArgument('two', 'c');
        $this->fixture->whenIInvoke('three');
        $this->fixture->whenIInvoke_WithTheArgument('two', 'd');

        $this->fixture->thenTheHistoryShouldBe('
one (1)
  (a, b) -> a

two (2)
  (c) -> returnTwoC
  (d) -> returnTwoD

three (1)
  () -> returnThree
');
    }

}