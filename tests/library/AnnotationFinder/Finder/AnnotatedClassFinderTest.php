<?php
// tests/library/AnnotationFinder/Finder/SimpleFinderTest.php
namespace AnnotationFinder\Finder;

use AnnotationFinder;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

class AnnotatedClassFinderTest extends \PHPUnit_Framework_TestCase
{

    protected $path;
    
    protected $annotation;

    protected $annotatedClassFinder;
   

    /**
     *
     * {@inheritdoc}
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp()
    {
        $this->path = __DIR__ . '/../../../stub/';
        $this->annotation = "@AnnotationFinder\AnnotationExample";
        $this->annotatedClassFinder = new AnnotatedClassFinder(new EventDispatcher());
    }

    /**
     * @test
     */
    public function testIfGetAnnotatedClassesFromPath()
    {
        $classes = $this->annotatedClassFinder->find($this->annotation)->in($this->path);
        $this->assertEquals(2, count($classes));    
    }
        
    /**
     * @test
     */
    public function testIfDispatchEventWhenFoundAnnotatedClass() {
        $callback = function(Event $event) {
            $this->assertInstanceOf("AnnotationFinder\Event\AnnotatedClassFoundEvent", $event);
        };
                
        $dispatcher = $this->getMockBuilder("Symfony\Component\EventDispatcher\EventDispatcher")->getMock();
        $dispatcher->expects($this->exactly(2))
            ->method("addListener")
            ->with("finds_{$this->annotation}", $callback);
        
        $dispatcher->expects($this->exactly(2))
            ->method("dispatch");
        
        $annotatedClassFinder = new AnnotatedClassFinder($dispatcher);
        $annotatedClassFinder->when("finds_{$this->annotation}", $callback);
        $annotatedClassFinder->find($this->annotation)->in($this->path);
    }
}