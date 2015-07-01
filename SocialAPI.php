<?php
abstract class SocialAPI {
  /**
   * @return int|bool Идентификатор города, false - если не нашел
   */
  abstract function findCity($city);
  
  abstract function findUser($name, $city_id = false);
  
  abstract function getUsersByIds(array $ids);
  
  abstract function getAvatars($id);
}
