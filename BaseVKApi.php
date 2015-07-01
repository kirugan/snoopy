<?php
abstract class BaseVKApi {
  protected $access_token;
  
  abstract public function api($method, $params);
}
