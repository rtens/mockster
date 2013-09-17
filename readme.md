# Mockster #

*mockster* is a full-fledged, zero-configuration mocking framework for PHP.

## Main Features ##

- Automatic mocking of dependencies, return values, method and constructor arguements
- Support of [BDD]-style testing by defining the context first and asserting expectations second

[BDD]: http://de.wikipedia.org/wiki/Behavior_Driven_Development

## Installation ##

You will need [Composer], [PHP] and [git] to download the project

    php composer.phar create-project rtens/mockster

or add it as a requirement to your projects `composer.json`

    "rtens/mockster": "*"
	
To run the test suite just execute `phpunit` in the base folder of mockster.

[Composer]: http://getcomposer.org/download/
[PHP]: http://php.net/downloads.php
[git]: http://git-scm.com/downloads

## Basic Usage ##

First, get a factory instance (let's assume the class definitions below as given).

	$factory = new MockFactory();
	
To get a completely detached mock which is but a hollow shell of its base class, use

	$mock = $factory->createMock('MyClass');
	
The created instance does not call its parent's class constructor, nor does any method call actually reach the parent. 

To call the parent's constructor, pass an array with constructor arguments, at least zero.

	$mock = $factory->createMock('MyClass', array('name' => 'Foo'));
	
This will call the constructor of `MyClass` with `'Foo'` and a clean mock of `YourClass` as parameters. Because of the type hint, an empty string would be passed if the `name` parameter was not given. The type hint in the constructor's doc comment is redundant since the method signatur already contains the type. Constructor arguments are accessible with `$mock->__mock()->getConstructorArgument('name')` or `$mock->__mock()->getConstructorargument(0)`.

Most classes have dependencies to other classes which usually have to be individually mocked away so only one class is tested. With mockster, mocks for properties can be generated automatically using

	$mock->__mock()->mockProperties();
	
Although any method call on this mock still doesn't actually do anything besides logging the invokation and returning a value corresponding to the `@return` type hint (mocked, if object). To actually invoke the methods of the mocked class, we have to tell the mock which methods we want not to be mocked.

	$myMethod = $mock->__mock()->method('myMethod');
	$myMethod->dontMock();
	
If we called `$mock->myMethod()`, the result would be `'Real'`. We can also tell the method to return anything else.

	$myMethod->willReturn('Fake')->withArguments('Test')->once();
	
Usually, all properties but no methods of a test unit should not be mocked. Such an instance can be created using the convenience method
	
	$uut = $factory->createTestUnit('MyClass');
	
To make assertions about how the tested object behaved, all method calls are logged and can be queried.

	$myMethod->wasCalled();
	$myMethod->wasCalledWith(array('arg' => 'Test'));
	$myMethod->wasCalledWith(array('Test'));
	$myMethod->getCalledCount();
	$myMethod->getHistory();

This is just a small part of the features. For a more detailed and up-to-date (maximum detailed and up-to-date, actually) description check out the [spec directory][spec].
	
At last, the class defintitions these examples are based on.

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

[spec]: https://github.com/rtens/mockster/tree/master/spec/rtens/mockster/

## Contribution ##

I'm looking forward to any kind of contribution including feedback about how unnecessary this project is, bugs and suggestions for missing features.