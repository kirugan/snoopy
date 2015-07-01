<?php
abstract class VKApiDecorator extends BaseVKApi{
  protected $_component;
  
  public function __construct(BaseVKApi $api) {
    $this->_component = $api;
  }
}