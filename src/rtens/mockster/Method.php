<?php
namespace rtens\mockster;
use rtens\mockster\behaviour\ReturnValueBehaviour;
use rtens\mockster\behaviour\CallbackBehaviour;
use rtens\mockster\behaviour\ThrowExceptionBehaviour;
use watoki\factory\ClassResolver;

/**
 * A mocked method collects all its invocations and forwards them to a Behaviour if set.
 *
 * Arguments are saved with their parameter names but can also be accessed via their position.
 */
class Method {

    /**
     * @var \ReflectionMethod
     */
    private $reflection;

    /**
     * @var array|Behaviour[] Registered behaviours
     */
    private $behaviours = array();

    /**
     * @var boolean
     */
    private $mocked = true;

    /**
     * @var MockFactory
     */
    private $factory;

    /** @var History */
    private $history;

    /**
     * @param \rtens\mockster\MockFactory $factory
     * @param \ReflectionMethod $reflection
     */
    public function __construct(MockFactory $factory, \ReflectionMethod $reflection) {
        $this->factory = $factory;
        $this->reflection = $reflection;
        $this->history = new History($reflection);
    }

    /**
     * Called when the method is invoked.
     *
     * @param array $arguments List of arguments
     * @return mixed The return value
     */
    public function invoke($arguments) {
        $named = array();
        foreach ($this->reflection->getParameters() as $param) {
            if (array_key_exists($param->getPosition(), $arguments)) {
                $named[$param->getName()] = $arguments[$param->getPosition()];
            }
        }

        foreach ($this->behaviours as $behaviour) {
            if ($behaviour->appliesTo($named)) {
                $value = $behaviour->getReturnValue($arguments);
                $this->history->log($arguments, $value);
                return $value;
            }
        }

        $value = $this->getReturnTypeHintMock();
        $this->history->log($arguments, $value);
        return $value;
    }

    /**
     * @throws \Exception
     * @return array|bool|float|int|Mock|null|string
     */
    public function getReturnTypeHintMock() {
        $matches = array();
        $found = preg_match('/@return\s+(\S+)/', $this->reflection->getDocComment(), $matches);

        if (!$found) {
            return null;
        }

        return $this->getInstanceFromHint($matches[1], $this->reflection->getDeclaringClass());
    }

    /**
     * Returns a mock or default value based on a type hint which may contain several classes spearated by |
     *
     * e.g.
     * string[]|array => array()
     * null|string => null
     * string|null => ''
     *
     * @param string $hint
     * @param \ReflectionClass $class
     * @throws \InvalidArgumentException
     * @return Mock|null|array|bool|float|int|string
     */
    public function getInstanceFromHint($hint, \ReflectionClass $class) {
        if (!$hint) {
            return null;
        }

        foreach ($this->explodeMultipleHints($hint) as $typeHint) {
            $resolver = new ClassResolver($class);
            $className = $resolver->resolve($typeHint);
            if ($className) {
                return $this->factory->getInstance($className);
            }

            try {
                return $this->getPrimitiveFromHint($typeHint);
            } catch (\InvalidArgumentException $e) {
            }
        }

        return null;
    }

    /**
     * Creates a mocked object or default value for given class or primitive.
     *
     * @param string $type
     * @return array|bool|float|int|Mock|null|string
     * @throws \InvalidArgumentException
     */
    private function getPrimitiveFromHint($type) {
        switch (strtolower($type)) {
            case 'array':
                return array();
            case 'int':
            case 'integer':
                return 0;
            case 'float':
                return 0.0;
            case 'bool':
            case 'boolean':
                return false;
            case 'string':
                return '';
            case 'null':
            case 'mixed':
            case 'object':
            case 'callable':
            case 'void':
            case 'closure':
                return null;
        }

        throw new \InvalidArgumentException("Not a primitive type [$type].");
    }

    private function explodeMultipleHints($hint) {
        if (strpos($hint, '|') !== false) {
            return explode('|', $hint);
        } else {
            return array($hint);
        }
    }

    /**
     * @param bool $mocked
     * @return Method
     */
    public function setMocked($mocked = true) {
        $this->mocked = $mocked;
        return $this;
    }

    /**
     * Sets the method to not-mocked
     */
    public function dontMock() {
        $this->setMocked(false);
    }

    /**
     * @return bool
     */
    public function isMocked() {
        return $this->mocked;
    }

    /**
     * @return string Name of the mocked method
     */
    public function getName() {
        return $this->reflection->getName();
    }

    /**
     * @return History
     */
    public function getHistory() {
        return $this->history;
    }

    /**
     * Will forward an invokation to given Behaviour if it applies.
     *
     * @param Behaviour $doThis
     * @return Behaviour
     */
    public function willDo(Behaviour $doThis) {
        array_unshift($this->behaviours, $doThis);
        $this->setMocked();
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

}

?>
