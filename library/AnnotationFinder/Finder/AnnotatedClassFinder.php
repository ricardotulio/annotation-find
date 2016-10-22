<?php
// library/AnnotationFinder/Finder/AnnotatedClassFinder.php
namespace AnnotationFinder\Finder;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\EventDispatcher\EventDispatcher;
use AnnotationFinder\Event\AnnotatedClassFoundEvent;

/**
 * @author ricardo
 */
final class AnnotatedClassFinder implements Finder
{

    private $pattern = null;

    private $path = null;

    private $events = array();

    private $dispatcher = null;

    public function __construct(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    private function hasClass($file) : bool {
        $tokens = token_get_all(file_get_contents($file));
                
        foreach($tokens as $token) {
            if(is_array($token) && in_array("class", $token)) {
                return true;         
            }
        }
        
        return false;
    }
    
    private function getDeclaredClasses($file) : array {
        $tokens = token_get_all(file_get_contents($file));
        $namespace = "";
        $class = "";
        $classes = array();
        
        foreach($tokens as $key => $value) {
            if(is_array($value)) {
                if(in_array(T_NAMESPACE, $value)) {
                    $namespace = $tokens[$key + 2][1];
                }
                
                if(in_array(T_CLASS, $value)) {
                    $class = $namespace . "\\" . $tokens[$key + 2][1];
                    array_push($classes, $class);
                }
            }
        }
        
        return $classes;
    }
    
    private function getClassAnnotation($class, $annotation) {
        $reflection = new \ReflectionClass($class);
        $annotationReader = new AnnotationReader();
        $annotation = $annotationReader->getClassAnnotation($reflection, str_replace('@', '\\', $this->pattern));
        
        return $annotation;
    }
    
    private function notifyAll($annotation, $class) {
        $event = new AnnotatedClassFoundEvent($class, array($annotation));
        $this->dispatcher->dispatch("finds_{$this->pattern}", $event);
    }
    
    public function find($pattern = "*"): Finder
    {
        $this->pattern = $pattern;
        return $this;
    }
    
    public function in($path = __DIR__): array
    {
        $classes = array();
        $finder = new \Symfony\Component\Finder\Finder();
        $finder->files('/.php$/')->in($path);
        
        foreach ($finder as $file) {
            if($this->hasClass($file)) {
                $classes = $this->getDeclaredClasses($file);
                
                foreach($classes as $class) {
                    $annotation = $this->getClassAnnotation($class, $this->pattern);
                    
                    if ($annotation != null) {
                        $this->notifyAll($annotation, $class);
                        array_push($classes, $class);
                    }
                }
            }
        }
                
        return $classes;
    }

    public function when($event, $callback)
    {
        $this->dispatcher->addListener($event, $callback);
    }
}

?>
