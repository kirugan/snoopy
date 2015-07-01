<?php
class VKSocialAPI extends SocialAPI{
  protected $_api_client;
  
  public function __construct(BaseVKApi $client){
    $this->_api_client = $client;
  }
  
  public function findCity($city) {
    $city_id = false;
    
    if(!$city){
      return $city_id;
    }
    
    $response = $this->_api_client->api('database.getCities', ['q' => $city, 'need_all' => 1, 'country_id' => 1]);
//  @todo реализация херовая - продумать лучше!
    foreach($response['items'] as $_city){
      if($_city['title'] === $city){
        $city_id = $_city['id'];
        break;
      }
    }
    
    return $city_id;
  }
  
  public function findUser($name, $city_id = false) {
    if($city_id === false){
      $data = $this->_api_client->findUser($name);
    } else {
      $data = $this->_api_client->findUser($name, $city_id);
    }
    
    return $data;
  }

  public function getUsersByIds(array $ids) {
//    echoln("USE LOCAL DATABASE");
    $r = $this->_api_client->api('users.get', ['user_ids' => $ids, 'fields' => 'photo_50, city']);
//  снизу костыль для поддержки 'count', 'items' формата ответа из users.search
    
    return ['items' => $r, 'count' => count($r)];
  }

  public function getAvatars($id) {
    $r = $this->_api_client->api('photos.getProfile', ['owner_id' => $id, 'rev' => 1]);
//    echo "getAvatars\n";
//    print_r($r);
    
    return $r['items'];
  }

}
