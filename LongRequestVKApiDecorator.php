<?php
class LongRequestVKApiDecorator extends VKApiDecorator{
  const CHUNK_SIZE = 100;
  
  public function api($method, $params) {
//    перенести логику замера внутрь usersGet ?
    if($method === 'users.get' && count($params['user_ids']) > self::CHUNK_SIZE){
      return $this->usersGet($params);
    }
    
    return $this->_component->api($method, $params);
  }
  
  protected function usersGet($params){
    $ret = [];
    $ids = $params['user_ids'];
    $chunks = array_chunk($ids, self::CHUNK_SIZE);
    foreach($chunks as $chunk){
      $_params = $params;
      $_params['user_ids'] = $chunk;
      $chunk_res = $this->_component->api('users.get', $_params);
      $ret = array_merge($ret, $chunk_res);
    }
    
    return $ret;
  }
  
  public function __call($name, $arguments) {
    return call_user_func_array([$this->_component, $name], $arguments);
  }
}
