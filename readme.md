# Mockster [![Build Status](https://travis-ci.org/rtens/mockster.png?branch=master)](https://travis-ci.org/rtens/mockster)

*mockster* is a full-fledged, zero-configuration [mocking] framework for PHP.

## Main Features ##

- Automatic mocking of dependencies, return values, method and constructor arguements
- Support of [BDD]-style testing by defining the context first and asserting expectations second
- Fine-grained configuration of the behaviour

[BDD]: http://de.wikipedia.org/wiki/Behavior_Driven_Development

## Installation ##

You will need [Composer], [PHP] and [git] to download the project

    php composer.phar create-project rtens/mockster

or add it as a requirement to your projects `composer.json`

    "rtens/mockster": "*"
	
To run the test suite just execute `phpunit` in the base folder of mockster.

    cd mockster
    phpunit

[mocking]: http://en.wikipedia.org/wiki/Mock_object
[Composer]: http://getcomposer.org/download/
[PHP]: http://php.net/downloads.php
[git]: http://git-scm.com/downloads

## Quick Start ##

A typical test with mockster looks like this.

```php
$factory = new MockFactory();                           // Where all Mocks come from
$uut = $factory->getInstance('MyUnitUnderTest');        // Creates a mock of MyUnitUnderTest
$uut->__mock()->mockProperties();                       // Creates mocks for properties based on type hints
$uut->__mock()->mockMethods(Mockster::F_NONE);          // No method should be mocked/stubbed
$dbMock = $uut->__mock()->get('database');              // Get reference to mocked property

$user = $factory->getInstance('User');
$dbMock->__mock()->method('readUser')->willReturn($user); // stubs method to return mock

$uut->updateUserName('Bart');                           // executes code to be tested

// asserts expected behaviour
$this->assertEquals('Bart', $user->__mock()->method('setName')->getHistory()->getArgument(0, 'name'));
$this->assertTrue($dbMock->__mock()->method('update')->getHistory()->wasCalledWith('user' => $user)));
```

## Basic Usage ##

First, we need an instance of MockFactory. It extends [Factory] so it supports singletons and providers (if needed).

```php
$factory = new MockFactory();
```
	
To get a completely empty mock which is but a hollow shell of the given class, use

```php
$mock = $factory->getInstance('MyClass');
```
	
The created instance extends the given class but does not invoke its parent's constructor, nor does any method call actually reach the parent - they are all mocked.

If you want to call the parent's constructor, pass an array with the constructor arguments. If you don't want to pass any arguments, provide an empty array.

```php
$mock = $factory->getInstance('MyClass', array('name' => 'Foo'));
```
	
You don't have to pass all constructor arguments since mocks are created for omitted arguments if a type hint is available either in the method signature or doc comment. Therefore, the above example invokes the constructor of `MyClass` with `'Foo'` and an empty mock of `YourClass` as parameters (see class definitions below). Notice that only objects can be mocked, not primitives such as integers, strings or arrays. Constructor arguments are accessible with `$mock->__mock()->getConstructorArgument('name')` or `$mock->__mock()->getConstructorArgument(0)`.

Most classes have dependencies to other classes which usually have to be mocked away individually. With *mockster*, dependencies injected into the constructor are mocked automatically and also can be injected into properties using

```php
$mock->__mock()->mockProperties();
```
	
By default, all properties without a default value are mocked. You can specify which properties should be mocked by either providing a filter bit-mask as first arguments or a filter function as second argument.
	
```php
$mock->__mock()->mockProperties(Mockster::F_PROTECTED | Mockster::F_PRIVATE);
$mock->__mock()->mockProperties(Mockster::F_ALL, function (\ReflectionProperty $p) {
	return strpos('@inject', $p->getDocComment()) !== false;
});
```
	
If you call any method (e.g. `$mock->foo()`), the invocation is logged and the return value is inferred from a `@return` type hint (if provided). In the case of `foo`, an empty string is returned because of the `string` hint. If the type hint is a class, an empty mock of this class is created. For example the call `$mock->__mock()->get('yours')->bar()` returns a mock of `MyClass`.

If you want certain methods to actually be invoked on the base class, you can un-mock methods.

```php
$foo = $mock->__mock()->method('foo');
$foo->dontMock();
```
	
Now the return value of `$mock->foo('bar')`, is `'foobar'`. You can also configure methods to return a fix value, throw a certain exception or call a callback. A method is implicitly mocked when its behaviour is configured.

```php
$foo->willReturn('bar')->withArguments('foo')->once();
$foo->willThrow(new \Exception);
$foo->willCall(function ($arg) { return $arg; })->with(array('arg' => 'foo'));
```

If multiple behaviours are defined for a method, they are applied in a last-in-first-out manner if they apply to the given arguments. This way, behaviours with a broad application can be overwritten with a more specific application and vice versa.
	
All of the above code only defines the behaviour of the mock without any expectations. To make assertions about how the object was interacted with, all method calls are logged and its history can be queried.

```php
$foo->getHistory()->wasCalled();
$foo->getHistory()->wasCalledWith(array('arg' => 'foo'));
$foo->getHistory()->wasCalledWith(array('foo'));
$foo->getHistory()->getCalledCount();
$foo->getHistory()->getCalledArguments();
```

These are the most basic features of mockster. For a more detailed and up-to-date (maximum detailed and up-to-date, actually) description check out the [spec directory][spec].
	
At last, the class definitions these examples are based on.

```php
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
	  * @return string
	  */
	public function foo($arg = '') {
		return 'foo' . $arg;
	}
}

class YourClass {

	/**
	  * @return MyClass
	  */
	public function bar() { }
}
```

[spec]: https://github.com/rtens/mockster/tree/master/spec/rtens/mockster/
[Factory]: https://github.com/watoki/factory/

## Contribution ##

I'm looking forward to any kind of contribution including feedback about how unnecessary this project is, bugs and suggestions for missing features.
