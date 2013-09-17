<?php
namespace rtens\mockster;

class AnnotationParser {

    private $docComment;

    public function __construct($docComment) {
        $this->docComment = $docComment;
    }

    public function find($annotation) {
        $all = $this->findAll($annotation);
        if (empty($all)) {
            return null;
        }
        return reset($all);
    }

    public function findAll($annotation) {
        $matches = array();
        if (preg_match_all('/@' . $annotation . '\s*([^\r\n]+)/', $this->docComment, $matches) == 0) {
            return array();
        }

        return (array) $matches[1];
    }

    /**
     * @param string $annotation
     * @return bool True if the given string contains @<annotation>
     */
    public function hasAnnotation($annotation) {
        return strpos($this->docComment, '@' . $annotation) !== false;
    }
}
