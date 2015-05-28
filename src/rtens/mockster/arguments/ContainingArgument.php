<?php
namespace rtens\mockster\arguments;

class ContainingArgument extends Argument {

    private $needle;

    function __construct($needle) {
        $this->needle = $needle;
    }

    public function accepts(Argument $argument) {
        if (!($argument instanceof ExactArgument)) {
            return false;
        }

        $value = $argument->value();

        if (is_array($value)) {
            return in_array($this->needle, $value);
        } else if (is_string($value)) {
            return strpos($value, $this->needle) !== false;
        } else if (is_object($value)) {
            foreach ($value as $item) {
                if ($item == $this->needle) {
                    return true;
                }
            }
        }

        return false;
    }
}