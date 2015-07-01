<?php
class VKSnoopy extends SocialSnoopy{
  const AVATAR_NONE_SUBSTR = '/images/camera_50.gif';
  const AVATAR_DEACTIVATED_SUBSTR = 'images/deactivated_50.gif';
  
  public function identify($params){
    $city_id = $this->_api->findCity($params['city']);
    $users = $this->_api->findUser($params['name'], $city_id);
    echoln("COUNT(USERS) => " . $users['count']);
    
    if($users['count'] > 50 || $users['count'] === 0){
      echoln("SKIP BECAUSE OF LIMITS {$users['count']}");
      return false;
    }
//    @todo Exception или false при отсутствии $users ?
    $users = $this->filterUsersWithoutAvatars($users);
//    DELETEME
//    print_r($users);
//    DELETEME
//    print_r($users['items']);
    $similar = $this->getSimilarUsers($users['items'], $params);
    if(count($similar) === 1){
      $ret = $similar[0];
    } else if(count($similar) === 0){
      $ret = false;
    } else {
      echoln("GOING DEEP!!!");
      if(count($users['items']) > 0){
        $ret = $this->identifyByDeepAvatarSearch($users['items']);
        print_r($ret);
      } else {
        die("FUCK");
      }
      if(!isset($ret)){
        $ret = false;
      }
//      echo "PARAMS\n";
//      print_r($params);
//      echo "USERS\n";
//      print_r($users['items']);
//      echo "RET\n";
//      print_r($ret);
//      die("DONT KNOW WHO IS WHO");
    }
    
    return $ret;
  }
  
  protected function identifyByDeepAvatarSearch($users){
    
  }
  
//  @todo переименовать
  protected function filterUsersWithoutAvatars(array $users){
    foreach($users['items'] as $k => $user){
      $avatar = $user['photo_50'];
      if(strpos($avatar, self::AVATAR_NONE_SUBSTR) !== false || strpos($avatar, self::AVATAR_DEACTIVATED_SUBSTR) !== false){
        unset($users['items'][$k]);
      } 
    }
    
    return $users;
  }
  
  protected function getSimilarUsers(array $users, array $params){
    $ret = [];
    
    foreach($users as $user){
      $avatar = $user['photo_50'];
      
      $ava_same = $this->isAvatarTheSame($avatar, $params['avatar']);
      if($ava_same){
        $similarity = 99;
        if(count($users) === 1){
          $similarity += 1000;
        }
        $ret[] = ['id' => $user['id'], 'similarity' => $similarity];
      }
    }
    /* DEEP AVATAR */
    if(count($ret) === 0 && strpos($params['avatar'], 'file://') === false){
      $ret = $this->getSimilarByProfilePhotos($users, $params);
    }
    /* DEEP AVATAR */
    
    return $ret;
  }
  
  protected function getSimilarByProfilePhotos($users, $params){
    $ret = [];
    $search_photo = $params['avatar'];
    $search_parts = parse_url($search_photo);
    
    foreach($users as $user){
      $avatars = $this->_api->getAvatars($user['id']);
      $is_similar = false;
      foreach($avatars as $avatar){
        $avatar_photo = $avatar['photo_75'];
        $avatar_parts = parse_url($avatar_photo);
        
//      example: AVATAR => http://cs10104.vk.me/u6650323/-6/s_b1215235.jpg SEARCH => http://cs10104.vk.me/v10104323/bc/4M6T74YDtuc.jpg
        if($avatar_parts['host'] === $search_parts['host']){
          $search_path = $search_parts['path'];
          $avatar_path = $avatar_parts['path'];
          $next_slash_pos = strpos($search_path, '/', 1);
          $compare = substr_compare($search_path, $avatar_path, 0, $next_slash_pos);
          
          if($compare === 0){
            var_dump($search_path, $avatar_path, $compare);
            echoln('GOOD USER => ' . $user['id'] . 'AVATAR => ' . $avatar_photo . ' SEARCH => ' . $search_photo);
            $is_similar = true;
          }
        }
      }
      if($is_similar){
        $ret[] = ['id' => $user['id'], 'similarity' => 90];
      }
    }
    
    return $ret;
  }
  
  protected function isAvatarTheSame($vk_avatar, $my_avatar){
    if($vk_avatar === $my_avatar){
      return true;
    }
    $vk_md5 = $this->md5($vk_avatar);
    $my_md5 = $this->md5($my_avatar);
    if($vk_md5 === $my_md5){
      return true;
    }
    
    
    return false;
  }
  
  protected function md5($filename){
    $max_attemts = 3;
//    @todo на самоме деле проверку можно опустить т.к. для файлов сразу должен пройти без фейлов
    if(strpos($filename, 'http://') !== false){
      $attemts = 0;
      do{
        $ret = $this->md5file($filename);
        if(gettype($ret) === 'string' || ($attemts < $max_attemts)){
          break;
        }
        $attemts++;
      }while(true);
    } else {
      $ret = $this->md5file($filename);
    }
    
    return $ret;
  }
  
  protected function md5file($filename){
    $c = curl_init();
    curl_setopt_array($c, [
        CURLOPT_URL => $filename,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5
    ]);
    
    $r = curl_exec($c);
    if($r !== false){
      return md5($r);
    } else {
      echoln("CURL FAILED for $filename");
      return false;
    }
  }
}











function isLookLike(array $user, array $vk_user, $deep = true){
  $camera_gif = '/images/camera_c.gif';
  $similarity = 0;
  
  if($user['avatar'] === $camera_gif){
    if(strpos($vk_user['photo_50'], $camera_gif) !== false){
      $similarity += 30;
    }
  } else if(strpos($user['avatar'], 'proxy') === false){
    if($vk_user['photo_50'] === $user['avatar']){
      $similarity += 80;
    } else if($deep){
      if(strpos($user['avatar'], 'AVATARS') !== false){
        $avatars_dir = sfConfig::get('sf_root_dir') . '/data/avatars';
        $avatar = str_replace('%AVATARS%', $avatars_dir, $user['avatar']);
      } else {
        $avatar = $user['avatar'];
      }
      $avatar__md5 = md5Avatar($avatar);
      $vk_user_photo_50__md5 = md5Avatar($vk_user['photo_50']);
      if($vk_user_photo_50__md5 === $avatar__md5){
        $similarity += 100;
      }
    }
  } else {
    $server = getServer($user['avatar']);
    
    if(strpos($vk_user['photo_50'], $server) !== false){
      $similarity += 30;
    }
  }
  
  if(isset($user['city']) && isset($vk_user['city']) && $user['city'] === $vk_user['city']['title']){
    $similarity += 30;
  }
  
  return $similarity;
}

function getServer($avatar){
  $m = [];
  $is_match = preg_match('/cs\d+\.vk/', $avatar, $m);
  if(!$is_match){
    die("avatar '$avatar' is not matched");
  }
  
  return $m[0];
}


function md5Avatar($url){
  static $cache = [];
  
  if(!isset($cache[$url])){
    $md5 = md5_file($url);
    if($md5 !== false){
      $cache[$url] = $md5;
    } else {
      echoln("MD5 FAILED - PROBABLY HTTP ERROR");
      return md5(microtime() . rand(0, 10));
    }
  }
  
  return $cache[$url];
}