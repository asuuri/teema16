<?php

class Twitter {
  
  public $requestUrl = 'https://stream.twitter.com/1.1/statuses/filter.json';

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

  private function buildSignature($dataArray) {
    $dataArray['track'] = $this->track;
    $dataArray['delimited'] = 'length';

    ksort($dataArray);

    $head = array(
      'GET',
      rawurlencode($this->requestUrl),
    );

    foreach ($dataArray as $key => $value) {
      $terms[] = rawurlencode($key) . '=' . rawurlencode($value);
    }

    $baseString =
      implode('&', $head) .
      '&' .
      rawurlencode(implode('&', $terms));

    $signingKey =
      rawurlencode($this->consumerSecret) .
      '&' .
      rawurlencode($this->accessSecret);

    return rawurlencode(base64_encode(
      hash_hmac('SHA1', $baseString, $signingKey, true)
    ));
  }

  private function buildOAuthHeader() {
    $data = array(
      'oauth_consumer_key' => $this->consumerKey,
      'oauth_nonce' => md5(rand(1000, 10000)),
      'oauth_signature_method' => 'HMAC-SHA1',
      'oauth_timestamp' => time(),
      'oauth_token' => $this->accessKey,
      'oauth_version' => '1.0',
    );

    $data['oauth_signature'] = $this->buildSignature($data);

    ksort($data);

    $headerArray = array();

    foreach ($data as $key => $value) {
      $headerArray[] = sprintf('%s="%s"', $key, $value);

    }

    return 'OAuth ' . implode(', ', $headerArray);
  }

  private function buildRequest($url) {
    
    $requestTemplate =
        "GET %s?delimited=length&track=%s HTTP/1.1\r\n" .
        "Host: %s\r\n" .
        "User-Agent: folower-riper 0.1\r\n" .
        "Authorization: %s\r\n\r\n";

    $oAuthHeader = $this->buildOAuthHeader();

    $request = sprintf(
      $requestTemplate,
      $url['path'],
      $this->track,
      $url['host'],
      $oAuthHeader
    );

    return $request;
  }

  public function connect() {
    $url = parse_url($this->requestUrl);
    $request = $this->buildRequest($url);
    $counter = 100;

    $scheme = $url['scheme'] === 'https'?'ssl://':'';
    $port = $url['scheme'] === 'https'?443:80;

    $this->handle = fsockopen($scheme . $url['host'], $port, $errno, $errstr);

    if (!$this->handle) {
      echo "$errstr ($errno)\n";
    } else {
      fwrite($this->handle, $request);

      while (!feof($this->handle) && $counter) {
        $length = trim(fgets($this->handle, 20));
        echo $length . "\n";

        $counter--;

        if (preg_match('/^[1-9][0-9]*$/', $length) && false) {
          $json = fread($this->handle, $length);

          echo $length . ' (' . strlen($json) . ")\n";
          echo $json . "\n";
          $counter--;
        }
      }

      $this->close();
    }
  }

  public function close() {
    fclose($this->handle);
  }
}
