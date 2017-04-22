<?php

namespace ntentan;

class Application {
    
    /**
     *
     * @var Context 
     */
    protected $context;
    private $pipeline = [middleware\MVC::class];
    
    /**
     * 
     * @param type $context
     */
    public function __construct(Context $context) {
        $this->context = $context;
    }
    
    public function getPipeline() {
        return $this->pipeline;
    }
    
    public function setup() {
        
    }
    
    public function appendMiddleware($class) {
        $this->pipeline[] = $class;
    }
    
    public function prependMiddleware($class) {
        $this->pipeline = [$class] + $this->pipeline;
    }
    
}
