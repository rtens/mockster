<?php
namespace spec\rtens\mockster;

use rtens\mockster\MockFactory;
use rtens\mockster\Mockster;
use watoki\scrut\Specification;

class IntroductionTest extends Specification {

    /**
     * A typical test with *mockster* might look like this.
     */
    public function testQuickStart() {
        $this->givenClassDefinitionsForQuickStart();

        /*
         * First create the instance of our *Unit Under Test* using the `MockFactory` which automatically
         * mocks all of its dependencies.
         */
        $factory = new MockFactory();
        $uut = $factory->getTestUnit('SomeClass');

        /*
         * Then configure the behaviour of the dependencies. Create a mock of the `User` entity and
         * use the injected `Database` mock.
         */
        $user = $factory->getInstance('User');
        $dbMock = $uut->__mock()->get('database');
        $dbMock->__mock()->method('readUser')->willReturn($user);

        // Now execute the code to be tested
        $uut->setUserName(1, 'Bart');

        // Last, asserts the expected behaviour
        $this->assertEquals('Bart', $user->__mock()->method('setName')->getHistory()->getCalledArgumentAt(0, 'name'));
        $this->assertTrue($dbMock->__mock()->method('update')->getHistory()->wasCalledWith(array('object' => $user)));
    }

    /**
     * Let's have a look at the parts of the described test in more detail.
     */
    public function testBasicUsage() {
        $this->givenClassDefinitionsForOverview();

        /*
         * Mocks are create by the `MockFactory`, extends [watoki/Factory] so it behaves a lot like a Dependency
         * Injection Provider. This means you can configure it using Singletons and Providers.
         *
         * [watoki/Factory]: https://github.com/watoki/factory/
         */
        $factory = new MockFactory();

        /*
         * To get a completely empty mock which is but a hollow shell of the given class, use
         */
        $mock = $factory->getMock('MyClass');

        /*
         * The created instance extends the given class but does not invoke its parent's constructor,
         * nor does any method call actually reach the parent - they are all mocked.
         *
         * If you want to call the parent's constructor, pass an array with the constructor arguments.
         * If you don't want to pass any arguments, provide an empty array.
         */
        $mockWithConstructor = $factory->getInstance('MyClass', array('name' => 'Foo'));

        /*
         * You don't have to pass all constructor arguments since mocks are created for omitted arguments if a
         * type hint is available either in the method signature or doc comment. Therefore, the above example
         * invokes the constructor of `MyClass` with `'Foo'` and an empty mock of `YourClass` as parameters
         * (see class definitions). Note that only objects can be mocked, not primitives such as integers,
         * strings or arrays. Constructor arguments are accessible with
         */
        $mockWithConstructor->__mock()->getConstructorArgument('name');
        // or
        $mockWithConstructor->__mock()->getConstructorArgument(0);

        /*
         * Most classes have dependencies to other classes which usually have to be mocked away individually. With
         * *mockster*, dependencies injected into the constructor are mocked automatically and also can be injected
         * into properties using
         */
        $mock->__mock()->mockProperties();

        /*
         * By default, all properties without a default value are mocked this way. You can specify which properties
         * should be mocked by either providing a filter bit - mask as first arguments or a filter function as second
         * argument.
         */
        $mock->__mock()->mockProperties(Mockster::F_PROTECTED | Mockster::F_NON_STATIC);
        $mock->__mock()->mockProperties(Mockster::F_ALL, function (\ReflectionProperty $p) {
            return strpos('@inject', $p->getDocComment()) !== false;
        });

        /*
         * If you call any method(e.g. `$mock->foo()`), the invocation is logged and the return value is inferred
         * from a `@return` type hint (if provided). In the case of `foo`, an empty string is returned because of the
         * `string` hint . If the type hint is a class, an empty mock of this class is created. For example, the call
         * `$mock->__mock()->get('yours')->bar()` returns a mock of `MyClass`.
         *
         * If you want certain methods to actually be invoked on the base class, you can un-mock methods .
         */
        $foo = $mock->__mock()->method('foo');
        $foo->dontMock();

        /*
         * Now the return value of `$mock->foo('bar')`, is `'foobar'`. You can also configure methods to return a
         * fix value, throw a certain exception or call a callback. A method is implicitly mocked when its behaviour
         * is configured.
         */
        $foo->willReturn('bar')->withArguments('foo')->once();
        $foo->willThrow(new \Exception);
        $foo->willCall(function ($arg) { return $arg; })->with(array('arg' => 'foo'));

        /*
         * If multiple behaviours are defined for a method, they are applied in a last-in-first-out manner
         * if they apply to the given arguments. This way, behaviours with a broad application can be overwritten
         * with a more specific application and vice versa.
         *
         * All of the above code only defines the behaviour of the mock without any expectations. To make assertions
         * about how the object was interacted with, all method calls are logged and its history can be queried.
         */
        $foo->getHistory()->wasCalled();
        $foo->getHistory()->wasCalledWith(array('arg' => 'foo'));
        $foo->getHistory()->wasCalledWith(array('foo'));
        $foo->getHistory()->getCalledCount();
        $foo->getHistory()->getCalledArguments();
    }

    public function testFurtherDocumentation() {
        /*
         * This project is documented using [Specification by Example][sbe] (aka [Behavior-Driven Development][bdd]).
         * You can find the executable specifications in the `spec` folder (along with this file).
         *
         * As a starting point, try `CreateMock` and `MethodBehaviour`. For making assertions check out
         * `RecordHistory`.
         *
         * [sbe]: http://specificationbyexample.com/
         * [bdd]: http://dannorth.net/introducing-bdd/
         */
        null;
    }

    private function givenClassDefinitionsForQuickStart() {
        eval('
            class SomeClass {

                /** @var Database */
                protected $database;

                public function setUserName($id, $name) {
                    $user = $this->database->readUser($id);
                    $user->setName($name);
                    $this->database->update($user);
                }

            }

            class DataBase {

                public function readUser($id) {
                    // [...]
                }

                public function update($object) {
                    // [...]
                }

            }

            class User {

                public function setName($name) {
                    // [...]
                }

            }
        ');
    }

    public function givenClassDefinitionsForOverview() {
        eval('
            class YourClass {

                /**
                 * @return MyClass
                 */
                public function bar() {
                }
            }

            class MyClass {

                /**
                 * @var YourClass
                 */
                protected $yours;

                /**
                 * @param string $name
                 * @param YourClass $yourClass
                 */
                public function __construct($name, YourClass $yourClass) {
                    // [...]
                }

                /**
                 * @param string $arg
                 * @return string
                 */
                public function foo($arg = "") {
                    return "foo" . $arg;
                }
            }'
        );
    }

} 