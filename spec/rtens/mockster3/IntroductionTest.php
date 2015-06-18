<?php
namespace spec\rtens\mockster3;

use rtens\mockster3\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;

class IntroductionTest extends StaticTestSuite {

    /**
     * A typical test with *mockster* might look like this.
     */
    public function testQuickStart() {
        /**
         * <a href="javascript:" onclick="$('#quickStartDefinitions').toggle();">
         * Show class definitions for this example
         * </a><div id="quickStartDefinitions" style="display: none;">
         */
        eval('
            class FooClass {

                /** @var MyDatabase <- */
                protected $database;

                public function setUserName($id, $name) {
                    $user = $this->database->readUser($id);
                    $user->setName($name);
                    $this->database->update($user);
                }
            }

            class MyDatabase {

                public function readUser($id) {
                    // [...]
                }

                public function update($object) {
                    // [...]
                }
            }

            class MyUser {

                public function setName($name) {
                    // [...]
                }
            }'
        );
        // </div>

        /*
         * First create `Mockster` instances of the classes we're gonna mock.
         */
        $foo = new Mockster('FooClass');
        $user = new Mockster('MyUser');

        /*
         * Then configure the behaviour of the dependencies of our *Unit Under Test*.
         *
         * The `Database` should return a mock of the `User` class when called with the argument `1`.
         */
        $userMock = $user->mock();
        $foo->database->readUser(1)->will()->return_($userMock);

        /*
         * Now execute the code to be tested.
         *
         * The `uut()` method will create an instance of the `FooClass` with
         * all it's dependencies replaced by mocks and none of it's methods stubbed.
         */
        $foo->uut()->setUserName(1, 'Bart');

        /*
         * Last, assert the expected behaviour.
         *
         * There should have been one call to `User::setName()` with the argument
         * `'Bart'` and one call on `Database::update()` with the `User` mock instance.
         */
        $this->assert($user->setName('Bart')->has()->beenCalled());
        $this->assert($foo->database->update($userMock)->has()->beenCalled());
    }

    public function testFurtherDocumentation() {
        /*
         * This project is documented using [Specification by Example][sbe] (aka [Behavior-Driven Development][bdd]).
         * You can find the executable specifications in the [`spec`][spec] folder (along with this file).
         *
         * You can find a browsable human-friendly export of this documentation on [dox].
         *
         * As a starting point, try [`CreateMocks`](CreateMocks) and [`StubMethods`](StubMethods). For
         * documentation on making assertions check out [`RecordCalls`](RecordCalls).
         *
         * [dox]: http://dox.rtens.org/rtens-mockster
         * [sbe]: http://specificationbyexample.com/
         * [bdd]: http://dannorth.net/introducing-bdd/
         * [spec]: https://github.com/rtens/mockster/tree/master/spec/rtens/mockster
         */
        $this->pass();
    }

}