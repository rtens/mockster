<?php
namespace rtens\mockster;

use ArrayIterator;
use rtens\mockster\behaviour\CallbackBehaviour;
use rtens\mockster\behaviour\ReturnValueBehaviour;
use rtens\mockster\behaviour\ThrowExceptionBehaviour;
use rtens\mockster\filter\Filter;
use Traversable;

class MethodCollection implements \Countable, \IteratorAggregate {

    /**
     * @var array|\ReflectionMethod[]
     */
    private $methods;

    /**
     * @var StubRegistry
     */
    private $stubRegistry;

    /**
     * @var MockFactory
     */
    private $factory;

    /**
     * @var string
     */
    private $className;

    /**
     * @param MockFactory $factory
     * @param $className
     * @param array|\ReflectionMethod[] $methods
     * @param StubRegistry $stubRegistry
     */
    public function __construct(MockFactory $factory, $className, array $methods = array(), StubRegistry $stubRegistry = null) {
        $this->factory = $factory;
        $this->className = $className;
        $this->methods = array_filter($methods, function (\ReflectionMethod $m) {
            return !$m->isPrivate() && !$m->isStatic();
        });
        $this->stubRegistry = $stubRegistry ? : new StubRegistry();
    }

    /**
     * @param bool $mocked
     * @return MethodCollection
     */
    public function setMocked($mocked = true) {
        foreach ($this->methods as $method) {
            $this->method($method->getName())->setMocked($mocked);
        }
        return $this;
    }

    /**
     * Sets the methods to not-mocked
     * @return MethodCollection
     */
    public function dontMock() {
        return $this->setMocked(false);
    }

    /**
     * @param int $verbosity
     * @return History
     */
    public function getHistory($verbosity = 0) {
        return array_reduce($this->stubRegistry->toArray(), function($history, Method $stub) use ($verbosity) {
            return $history . ($stub->getHistory()->wasCalled() ? PHP_EOL . $stub->getHistory()->toString($verbosity) : '');
        }, '');
    }

    /**
     * Will forward an invokation to given Behaviour if it applies.
     *
     * @param Behaviour $doThis
     * @return Behaviour
     */
    public function willDo(Behaviour $doThis) {
        foreach ($this->methods as $method) {
            $this->method($method->getName())->willDo($doThis);
        }
        return $doThis;
    }

    /**
     * @param mixed $value
     * @return \rtens\mockster\behaviour\ReturnValueBehaviour
     */
    public function willReturn($value) {
        return $this->willDo(new ReturnValueBehaviour($value));
    }

    /**
     * @param \Exception $exception
     * @return \rtens\mockster\behaviour\ThrowExceptionBehaviour
     */
    public function willThrow($exception) {
        return $this->willDo(new ThrowExceptionBehaviour($exception));
    }

    /**
     * @param \callable $callback
     * @return \rtens\mockster\behaviour\CallBackBehaviour
     */
    public function willCall($callback) {
        return $this->willDo(new CallbackBehaviour($callback));
    }

    /**
     * @param string $methodName
     * @return bool
     */
    public function isMocked($methodName) {
        return $this->stubRegistry->exists($methodName) && $this->stubRegistry->get($methodName)->isMocked();
    }

    /**
     * @param string $methodName
     * @return Method
     * @throws \InvalidArgumentException
     */
    public function method($methodName) {
        foreach ($this->methods as $method) {
            if ($method->getName() == $methodName) {
                if (!$this->stubRegistry->exists($methodName)) {
                    $this->stubRegistry->set($methodName, new Method($this->factory, $method));
                }
                return $this->stubRegistry->get($methodName);
            }
        }

        throw new \InvalidArgumentException(sprintf("Can't mock method %s::%s.",
            $this->className, $methodName));
    }

    /**
     * Gets all methods matching the filter
     *
     * @param int $filter Constants from Mockster::F_
     * @param \callable|null $customFilter
     * @return \rtens\mockster\MethodCollection
     */
    public function filter($filter = Mockster::F_ALL, $customFilter = null) {
        $filter = new Filter($filter, $customFilter);
        return new static($this->factory, $this->className, array_filter($this->methods, function(\ReflectionMethod $method) use ($filter) {
            return $filter->apply($method);
        }), $this->stubRegistry);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator() {
        return new ArrayIterator($this->methods);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count() {
        return count($this->methods);
    }
}