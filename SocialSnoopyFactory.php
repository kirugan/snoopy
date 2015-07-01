<?php
use Guzzle\Http\Client;

class SocialSnoopyFactory {
  const SOCNET_VK = 0;
  
  public static function createInstance($type){
    if($type === self::SOCNET_VK){
      $c = new Client();
      /* ACCESS TOKEN */
      $config = AppConfigFactory::getInstance()->getNext();
      print_r($config);
      $access_token = BotService::getInstance()->getAccessToken($config['id'], $config['secret'], $config['permissions'], $config['redirect_url']);
      
      echoln("ACCESS_TOKEN => $access_token");
      if(!$access_token){
        die('DIED' . PHP_EOL);
      }
      /* /ACCESS_TOKEN */
//      @todo перенети в фабрику SocialApi
      $_api = new VKApi($c, $access_token);
      /* LOG */
      $_api = new LogVKApiDecorator($_api);
      /* /LOG */
      /* CACHE */
      $_api = new CacheVKApiDecorator($_api);
      /* /CACHE */
      $_api = new TimeoutVKApiDecorator($_api);
      $api = new LongRequestVKApiDecorator($_api);
      $client = new LocalDataSocialAPIDecorator(new VKSocialAPI($api));
      
      $ret = new VKSnoopy($client);
    } else {
      throw new InvalidArgumentException("Type '$type' is not implemented");
    }
    
    return $ret;
  }
}
