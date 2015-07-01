<?php
/**
 * Description of LogVKApiDecorator
 *
 * @author kirill
 */
class LogVKApiDecorator extends VKApiDecorator{
  private $counter = 0;
  
  public function api($method, $params) {
    @$params_str = join(',', $params);
    $this->log("API: $method ($params_str)");
    
    $this->counter++;
    $this->log("Total API Requests $this->counter");
    
    return $this->_component->api($method, $params);
  }
  
  protected function log($msg){
    echoln($msg);
  }
  
  public function __call($name, $arguments) {
    return call_user_func_array([$this->_component, $name], $arguments);
  }
}
