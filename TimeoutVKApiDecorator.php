<?php
class TimeoutVKApiDecorator extends VKApiDecorator{
  private $counters = [];
  
  public function api($method, $params) {
    $this->lock();
    $r = $this->apiIgnoreCallLimits($method, $params);
    $this->updateCounter();
    
    return $r;
  }
  
  private function lock(){
    $time = time();
    if(!isset($this->counters[$time])){
      $this->counters[$time] = 0;
    }
    
//    echoln("LOCK VALUE $time => {$this->counters[$time]}");
    
    if($this->counters[$time] > 4){
      $sleep = 1;
      echoln("TIMELOCK FOR $sleep sec.");
      $this->sleep($sleep);
    }
  }
  
  private function updateCounter(){
    $time = time();
    if(!isset($this->counters[$time])){
      $this->counters[$time] = 0;
    }
    
    $this->counters[$time]++;
  }
  
  protected function apiIgnoreCallLimits($method, $arguments){
    try{
      $ret = $this->_component->api($method, $arguments);
    } catch (VKApiException $e) {
      if($e->getCode() === 6){
        $this->longSleep();
        return $this->apiIgnoreCallLimits($method, $arguments);
      } else {
        throw $e;
      }
    }
    
    return $ret;
  }
  
  public function __call($method, $arguments){
    try{
      $r = call_user_func_array([$this->_component, $method], $arguments);
    } catch (VKApiException $e) {
      $this->longSleep();
      $r = $this->__call($method, $arguments);
    }
    
    return $r;
  }
  
  protected function longSleep(){
    $sleep = 4;
    $this->sleep($sleep);
    echoln("ВНИМАНИЕ!!! Долгий сон. На $sleep секунд. У них есть лимиты на определенные методы.");
    echoln("Они их не раскрывают. НО!!! Позже надо выяснить какие лимиты на методы. Чтобы быть более");
    echoln("мобильным.");
  }
  
  protected function sleep($time){
    $this->total_sleep += $time;
    sleep($time);
    echoln("TOTAL SLEEP $this->total_sleep");
  }
}
