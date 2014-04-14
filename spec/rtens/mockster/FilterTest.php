<?php
namespace spec\rtens\mockster;

use rtens\mockster\Mockster;
use watoki\scrut\Specification;

/**
 * @property MockFactoryFixture fixture <-
 * @property FilterFixture filter <-
 */
class FilterTest extends Specification {

    public function testMatchAll() {
        $this->fixture->givenTheClassDefinition('
            class MatchAll {
                public function myFunction() {}

                protected function myFunctionProtected() {}

                private function myFunctionPrivate() {}
            }
        ');
        $this->filter->givenTheFilterWithTheBitMask(Mockster::F_ALL);
        $this->filter->whenTheFilterIsAppliedToTheMethodsOfClass('MatchAll');
        $this->filter->thenFilterMatchesContainTheMethod('myFunction');
        $this->filter->thenFilterMatchesContainTheMethod('myFunctionProtected');
        // private members are ignored
        $this->filter->thenFilterMatchesDoesNotContainTheMethod('myFunctionPrivate');
        $this->filter->thenTheFilterMatched_Methods(2);
    }

    public function testMatchPublic() {
        $this->fixture->givenTheClassDefinition('
            class MatchPublic {
                public function myFunction() {}

                protected function myFunctionProtected() {}
            }
        ');
        $this->filter->givenTheFilterWithTheBitMask(Mockster::F_PUBLIC);
        $this->filter->whenTheFilterIsAppliedToTheMethodsOfClass('MatchPublic');
        $this->filter->thenFilterMatchesContainTheMethod('myFunction');
        $this->filter->thenTheFilterMatched_Methods(1);
    }

    public function testMatchProtected() {
        $this->fixture->givenTheClassDefinition('
            class MatchProtected {
                public function myFunction() {}

                protected function myFunctionProtected() {}
            }
        ');
        $this->filter->givenTheFilterWithTheBitMask(Mockster::F_PROTECTED);
        $this->filter->whenTheFilterIsAppliedToTheMethodsOfClass('MatchProtected');
        $this->filter->thenFilterMatchesContainTheMethod('myFunctionProtected');
        $this->filter->thenTheFilterMatched_Methods(1);
    }

    public function testMatchProtectedOrPublic() {
        $this->fixture->givenTheClassDefinition('
            class MatchProtectedOrPublic {
                public function myFunction() {}

                protected function myFunctionProtected() {}
            }
        ');
        $this->filter->givenTheFilterWithTheBitMask(Mockster::F_PUBLIC | Mockster::F_PROTECTED);
        $this->filter->whenTheFilterIsAppliedToTheMethodsOfClass('MatchProtectedOrPublic');
        $this->filter->thenFilterMatchesContainTheMethod('myFunction');
        $this->filter->thenFilterMatchesContainTheMethod('myFunctionProtected');
        $this->filter->thenTheFilterMatched_Methods(2);
    }

    public function testMatchNone() {
        $this->fixture->givenTheClassDefinition('
            class MatchNone {
                public function myFunction() {}

                protected function myFunctionProtected() {}
            }
        ');
        $this->filter->givenTheFilterWithTheBitMask(Mockster::F_NONE);
        $this->filter->whenTheFilterIsAppliedToTheMethodsOfClass('MatchNone');
        $this->filter->thenTheFilterMatched_Methods(0);
    }
} 