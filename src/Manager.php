<?php
namespace Aura\Invoker;

use Closure;

class Manager
{
    use InvokeMethodTrait;
    
    protected $object_param = 'object';
    
    protected $method_param = 'method';
    
    protected $object;
    
    protected $method;
    
    protected $params;
    
    public function __construct(
        ObjectFactory $object_factory,
        $object_param = null,
        $method_param = null
    ) {
        $this->object_factory = $object_factory;
        if ($object_param) {
            $this->object_param = $object_param;
        }
        if ($method_param) {
            $this->method_param = $method_param;
        }
    }
    
    public function exec(array $params = [], $object = null, $method = null)
    {
        $this->params = $params;
        $this->object = $object;
        $this->method = $method;
        
        $this->fixObject();
        $this->fixMethod();
        
        return $this->invokeMethod(
            $this->object,
            $this->method,
            $this->params
        );
    }
    
    protected function fixObject()
    {
        // are we missing the object spec?
        if (! $this->object) {
            // no, set it from the params
            $this->object = isset($this->params[$this->object_param])
                          ? $this->params[$this->object_param]
                          : null;
        }
        
        // are we still missing the object spec?
        if (! $this->object) {
            throw new Exception\ObjectNotSpecified;
        }
        
        // is it actually an object?
        if (! is_object($this->object)) {
            // treat it as a spec for the factory
            $this->object = $this->object_factory($this->object);
        }
    }
    
    protected function fixMethod()
    {
        // if the object is a closure, override the method
        if ($this->object instanceof Closure) {
            $this->method = '__invoke';
        }
        
        // are we missing the method?
        if (! $this->method) {
            // set it from the params
            $this->method = isset($this->params[$this->method_param])
                          ? $this->params[$this->method_param]
                          : null;
        }
        
        // are we still missing the method?
        if (! $this->method) {
            throw new Exception\MethodNotSpecified;
        }
    }
}
