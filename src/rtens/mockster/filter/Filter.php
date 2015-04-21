<?php
namespace rtens\mockster\filter;

use rtens\mockster\Mockster;

class Filter {

    /**
     * @int
     */
    private $filter;

    /**
     * @callable
     */
    private $customFilter;

    /**
     * @param int $filter Using bit-combinations of Mockster::F_* (e.g. Mockster::F_PUBLIC | Mockster::F_PROTECTED)
     * @param callable|null $customFilter
     */
    public function __construct($filter, $customFilter = null) {
        $this->filter = $filter;
        $this->customFilter = $customFilter;
    }

    /**
     * @param \Reflector $member
     * @return bool
     */
    public function apply(\Reflector $member = null) {
        /* @var $member \ReflectionProperty */
        $customFilter = $this->customFilter;

        return
            !$member->isPrivate() &&
            (!$member->isPublic() || ($this->filter & Mockster::F_PUBLIC) == Mockster::F_PUBLIC) &&
            (!$member->isProtected() || ($this->filter & Mockster::F_PROTECTED) == Mockster::F_PROTECTED) &&
            (!$member->isStatic() || ($this->filter & Mockster::F_STATIC) == Mockster::F_STATIC) &&
            (!$customFilter || $customFilter($member));
    }
} 