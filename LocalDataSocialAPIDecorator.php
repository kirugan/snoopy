<?php
class LocalDataSocialAPIDecorator extends SocialAPIDecorator{
  public function findCity($city) {
    return $this->_component->findCity($city);
  }

  public function findUser($name, $city_id = false) {
    $ret = $this->_component->findUser($name, $city_id);
    /* KOSTIL */
    if(count($ret['items']) === 0){
      $ids = $this->getRedis()->smembers($name);
//      echoln("IDS FROM REDIS => " . count($ids));
      if(count($ids)){
        $ret = $this->getUsersByIds($ids);
      } else {
        $str = str_repeat('#', 50);
        echoln($str);
        echoln("Странно, пользователя нет в локальной базе и нет в API!!! '$name'");
        echoln($str);
        $ret = ['count' => 0, 'items' => []];
      }
    }
    /* /KOSTIL */
    
    return $ret;
  }

  public function getRedis(){
    static $redis = null;
    if($redis === null){
      $redis = new Predis\Client();
    }
    
    return $redis;
  }

  public function getUsersByIds(array $ids) {
    return $this->_component->getUsersByIds($ids);
  }

  public function getAvatars($id) {
    return $this->_component->getAvatars($id);
  }

//put your code here
}
