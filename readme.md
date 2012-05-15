# Mockster #

*mockster* is a straight-forward mocking framework for PHP.

## Main Features ##

- Automatic mocking of dependencies, return values and constructor arguements
- Support of [BDD][1] style by defining the context first and asserting expectations second

## Basic Usage ##

For more usage examples, see `mockster\MocksterTest`

	$factory = new MockFactory();

	$mock = $factory->createMock('MyClass');
	$mock->__mock()->method('myMethod')->willReturn('Test')->withArguments(1);

	$this->assertEquals('Test', $mock->myMethod(1));
	$this->assertEquals('', $mock->myMethod(2));

	$mock->__mock()->method('myMethod')->dontMock();

	$this->assertEquals('Real', $mock->myMethod(1));
	$this->assertTrue($mock->yourObject->yourMethod() instanceof MyClass);

Class definitions:

	class MyClass {
		
		/**
		  * @var YourClass
	      */
		protected $yourObject;

		/**
		  * @return string
		  */
		public function myMethod($arg) {
			return 'Real';
		}
	}

	class YourClass {

		/**
		  * @return MyClass
		  */
		public function yourMethod() { }
	}



[1]: http://de.wikipedia.org/wiki/Behavior_Driven_Development	"Behaviour Driven Development"

