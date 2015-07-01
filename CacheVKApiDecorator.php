<?php
/**
 * Description of CacheVKApiDecorator
 *
 * @author kirill
 */
class CacheVKApiDecorator extends VKApiDecorator{
  public function api($method, $params) {
    $params_str = serialize($params);
    $hash = md5($method . $params_str);
    $cache = $this->getCache();
    
    if(($ret = $cache->get($hash)) === false){
      $ret = $this->_component->api($method, $params);
      
      if($method === 'database.getCities'){
        $cache->set($hash, $ret);
      }
    }
    
    return $ret;
  }
  
  protected function getCache(){
    static $cache = null;
    if($cache === null){
      $cache = new Memcached();
      $cache->addServer('localhost', '11211');
    }
    
    return $cache;
  }
  
  public function __call($name, $arguments) {
    return call_user_func_array([$this->_component, $name], $arguments);
  }
}
