<?php

class Twitter {
  
  private $consumerKey = null;
  private $consumerSecret = null;

  private $accessKey = null;
  private $accessSecret = null;

  private $track = '';

  private $handle = null; 
  
  public function __construct($config = array()) {
    if (isset($config['consumerKey'])) {
      $this->consumerKey = $config['consumerKey'];  
    }

    if (isset($config['consumerSecret'])) {
      $this->consumerSecret = $config['consumerSecret'];  
    }

    if (isset($config['accessKey'])) {
      $this->accessKey = $config['accessKey'];  
    }

    if (isset($config['accessSecret'])) {
      $this->accessSecret = $config['accessSecret'];  
    }

    if (isset($config['track'])) {
      $this->track = $config['track'];  
    }
  }

  public function connect() {
    return false;  
  }

  public function close() {
    return false;  
  }
}
