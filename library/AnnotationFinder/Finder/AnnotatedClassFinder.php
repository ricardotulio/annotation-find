<?php
// library/AnnotationFinder/Finder/AnnotatedClassFinder.php
namespace AnnotationFinder\Finder;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\EventDispatcher\EventDispatcher;
use AnnotationFinder\Event\AnnotatedClassFoundEvent;

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
            $code = file_get_contents($file);
            $tokens = token_get_all($code);
            $namespace = "";
            $class = "";
            
            foreach ($tokens as $key => $token) {
                if (is_array($token)) {
                    if (in_array(T_NAMESPACE, $token)) {
                        $namespace = $tokens[$key + 2][1];
                    }
                    
                    if (in_array(T_CLASS, $token)) {
                        $class = $tokens[$key + 2][1];
                        $fqn = $namespace . '\\' . $class;
                        
                        if (class_exists($fqn)) {
                            $reflection = new \ReflectionClass($fqn);
                            $annotationReader = new AnnotationReader();
                            
                            if (($annotation = $annotationReader->getClassAnnotation($reflection, str_replace('@', '\\', $this->pattern))) != null) {
                                $event = new AnnotatedClassFoundEvent($fqn, array($annotation));
                                $this->dispatcher->dispatch("finds_{$this->pattern}", $event);
                                array_push($classes, $fqn);
                            }
                        }
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
