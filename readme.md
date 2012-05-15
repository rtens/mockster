# Mockster #

*mockster* is a full-fledged, zero-configuration mocking framework for PHP.

## Main Features ##

- Automatic mocking of dependencies, return values, method and constructor arguements
- Support of [BDD][1]-style testing by defining the context first and asserting expectations second

[1]: http://de.wikipedia.org/wiki/Behavior_Driven_Development	"Behaviour Driven Development"

## Basic Usage ##

First, get a factory instance (let's assume the class definitions below as given).

	$factory = new MockFactory();
	
To get a completely detached mock which is but a hollow shell of its base class, use

	$mock = $factory->createMock('MyClass');
	
The created instance does not call its parent's class constructor, nor does any method call actually reach the parent. 

To call the parent's constructor, pass an array with constructor arguments, at least zero.

	$mock = $factory->createMock('MyClass', array('name' => 'Foo'));
	
This will call the constructor of `MyClass` with `'Foo'` and a clean mock of `YourClass` as parameters. Because of the type hint, an empty string would be passed if the `name` parameter was not given. The type hint in the constructor's doc comment is redundant since the method signatur already contains the type. Constructor arguments are accessible with `$mock->mock()->getConstructorArgument('name')` or `$mock->mock()->getConstructorargument(0)`.

Most classes have dependencies to other classes which usually have to be carefully mocked away so only one class is tested. With mockster this can be done easily using

	$mock = $factory->createMock('MyClass', array(), true);
	
The third parameter tells the factory to create mocks for all instance variables with a type hint. Although any method call on this mock still doesn't actually do anything besides logging the invokation and returning a value corresponding to the `@return` type hint (a mock if a class). To actually reach the mocked class, we have to tell the mock which method we want not to be mocked.

	$myMethod = $mock->mock()->method('myMethod');
	$myMethod->dontMock();
	
If we called `$mock->myMethod()`, the result would be `'Real'`. We can also tell the method to return anything else.

	$myMethod->willReturn('Fake')->withArguments('Test')->once();
	
Usually, the entire public interface of a test unit should not be mocked. Such an instance can be created using the convenience method
	
	$uut = $factory->createTestUnit('MyClass');
	
To make assertions about how the tested object behaved, all method calls are logged and can be queried.

	$myMethod->wasCalled();
	$myMethod->wasCalledWith(array('arg' => 'Test'));
	$myMethod->wasCalledWith(array('Test'));
	$myMethod->getCalledCount();
	$myMethod->getHistory();

This is just a small part of the currently implemented features. For a more detailed (maximum detailled, actually) description check out `mockster\MocksterTest`.
	
At last the class defintitions these examples are based on.

	class MyClass {
		
		/**
		  * @var YourClass
	      */
		protected $yourObject;
		
		/**
		  * @param string $name
		  * @param YourClass $yourClass
		  */
		public function __construct($name, YourClass $yourClass) {
			// [...]
		}

		/**
		  * @return string
		  */
		public function myMethod($arg = null) {
			return 'Real';
		}
	}

	class YourClass {

		/**
		  * @return MyClass
		  */
		public function yourMethod() { }
	}

## Contribution ##

I'm looking forward to any kind of contribution including feedback about how unnecessary this project is, bugs and suggestions for missing features.