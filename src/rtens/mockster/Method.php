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
     * @var MethodTypeHint
     */
    private $typeHint;

    /**
     * @param \rtens\mockster\MockFactory $factory
     * @param \ReflectionMethod $reflection
     */
    public function __construct(MockFactory $factory, \ReflectionMethod $reflection) {
        $this->factory = $factory;
        $this->reflection = $reflection;
        $this->history = new History($reflection);
        $this->typeHint = new MethodTypeHint($factory, $reflection);
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
                if (!$this->typeHint->matchesTypeHint($value)) {
                    $actualType = is_object($value) ? get_class($value) : (gettype($value) === 'double' ? 'float' : gettype($value));
                    throw new \InvalidArgumentException('Expected return value of method ' . $this->reflection->getDeclaringClass()->getName() .
                        ':' . $this->getName() . ' to be of one of the following types: [' . implode(',', $this->typeHint->getTypeHints()) . ']. ' .
                    'Instead value is ' . $actualType);
                }
                $this->history->log($arguments, $value);
                return $value;
            }
        }

        $value = $this->typeHint->getDefaultValue();
        $this->history->log($arguments, $value);
        return $value;
    }

    /**
     * @return array|bool|float|int|Mock|null|string
     */
    public function getReturnTypeHintMock() {
        return $this->typeHint->getDefaultValue();
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