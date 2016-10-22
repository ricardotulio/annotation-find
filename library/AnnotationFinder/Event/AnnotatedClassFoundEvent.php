<?php
namespace AnnotationFinder\Event;

use Symfony\Component\EventDispatcher\Event;

final class AnnotatedClassFoundEvent extends Event
{
    private $class;

    private $annotations;

    public function __construct($class, $annotations = array())
    {
        $this->class = $class;
        $this->annotations = $annotations;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getAnnotations()
    {
        return $this->annotations;
    }
}