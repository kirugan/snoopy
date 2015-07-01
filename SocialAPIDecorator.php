<?php
abstract class SocialAPIDecorator extends SocialAPI{
  protected $_component;
  
  public function __construct(SocialAPI $api) {
    $this->_component = $api;
  }
}
