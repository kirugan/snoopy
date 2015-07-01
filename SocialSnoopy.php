<?php
abstract class SocialSnoopy {
  protected $_api;
  
  public function __construct(SocialAPI $api){
    $this->_api = $api;
  }
  
  abstract function identify($params);
}
