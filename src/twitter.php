<?php

class Twitter {
  
  const CRLF = "\r\n";
  const BUFFER_LENGTH = 8192;

  public $requestUrl = 'https://stream.twitter.com/1.1/statuses/filter.json?delimited=length&track=';

  private $consumerKey = null;
  private $consumerSecret = null;

  private $accessKey = null;
  private $accessSecret = null;

  private $fifoFile = null;

  private $track = '';

  private $handle = null;

  private $jsonLength = 0;
  private $json = '';
  
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

    if (isset($config['fifo_path'])) {
        if (is_file($config['fifo_path'])) {
            unlink($config['fifo_path']);
        }

        posix_mkfifo($config['fifo_path'], 0666);

        $this->fifoFile = fopen($config['fifo_path'], "w");
        stream_set_blocking($this->fifoFile, false);
    }
  }

  private function buildSignature($dataArray) {

    ksort($dataArray);

    $head = array(
      'GET',
      rawurlencode($this->requestUrl),
    );

    foreach ($dataArray as $key => $value) {
      $terms[] = rawurlencode($key) . '=' . rawurlencode($value);
    }

    $baseString =
      implode('&', $head) . '&' .
      rawurlencode(implode('&', $terms));

    $signingKey =
      rawurlencode($this->consumerSecret) .
      '&' .
      rawurlencode($this->accessSecret);

    return rawurlencode(base64_encode(
      hash_hmac('SHA1', $baseString, $signingKey, true)
    ));
  }

  private function buildOAuthHeader($url) {
    $data = array(
      'oauth_consumer_key' => $this->consumerKey,
      'oauth_nonce' => md5(rand(1000, 10000)),
      'oauth_signature_method' => 'HMAC-SHA1',
      'oauth_timestamp' => time(),
      'oauth_token' => $this->accessKey,
      'oauth_version' => '1.0',
    );

    if (isset($url['query'])) {
        parse_str($url['query'], $query);
        $data = array_merge($data, $query);
    }

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
        "GET %s?%s HTTP/1.1\r\n" .
        "Host: %s\r\n" .
        "User-Agent: folower-riper 0.1\r\n" .
        "Authorization: %s\r\n\r\n";

    $oAuthHeader = $this->buildOAuthHeader($url);

    $request = sprintf(
      $requestTemplate,
      $url['path'],
      isset($url['query'])?$url['query']:'',
      $url['host'],
      $oAuthHeader
    );

    print $request;
    die;

    return $request;
  }

  public function connect() {
    $this->readFirstOnes();
    die;
    $url = parse_url($this->requestUrl . $this->track);
    $request = $this->buildRequest($url);
    $counter = 100;

    $scheme = $url['scheme'] === 'https'?'ssl://':'';
    $port = $url['scheme'] === 'https'?443:80;

    $this->handle = fsockopen($scheme . $url['host'], $port, $errno, $errstr);

    if (!$this->handle) {
      echo "$errstr ($errno)\n";
    } else {
      fwrite($this->handle, $request);

      $this->readStream($counter);

      $this->close();
    }
  }

  public function readFirstOnes() {
    $urlStr =
        'https://api.twitter.com/1.1/search/tweets.json?' .
        'result_type=recent&count=5&q=' .
        $this->track;
    $url = parse_url($urlStr);

    $scheme = $url['scheme'] === 'https'?'ssl://':'';
    $port = $url['scheme'] === 'https'?443:80;

    var_dump($this->buildOAuthHeader($url));
    die;

    $curl = curl_init();
    curl_setopt_array(
        $curl,
        array(
            CURLOPT_URL => $urlStr,
            CURLOPT_HTTPHEADER => array(
                'User-Agent: folower-riper 0.1',
                'Authorization: ' . $this->buildOAuthHeader($url),
            )
        )
    );
    $content = curl_exec($curl);
    curl_close($curl);
    var_dump($content);
    die;


  }

  private function readStream($counter) {
    do {
        $data = fgets($this->handle, self::BUFFER_LENGTH);
        if ($data === false || $data == self::CRLF || feof($this->handle)) {
            break;
        }
    } while (true);

    do {
        $line = fgets($this->handle, self::BUFFER_LENGTH);

        if ($line == self::CRLF) {
            continue;
        }

        $length = hexdec($line);

        if (!is_int($length)) {
            trigger_error('Most likely not chunked encoding', E_USER_ERROR);
        }

        if ($line === false || $length < 1 || feof($this->handle)) {
            break;
        }

        do {
            $data = fread($this->handle, $length);

            $length -= strlen($data);
            $this->parseData($data);

            if ($counter > -1) {
                $counter--;
            }

            if ($length <= 0 || feof($this->handle) || $counter <= 0) {
                break;
            }
        } while (true);
        if ($counter == 0) {
            break;
        }
    } while (true);
  }

  private function parseData($data) {
        $dataLines = explode("\n", $data);
        foreach ($dataLines as $dataLine) {
            if (preg_match('/^[1-9][0-9]*$/', trim($dataLine))) {
                $this->sendJson();
                $this->jsonLength = $dataLine - 1;
                
            } else {
                $this->jsonLength -= strlen($dataLine);
                $this->json .= trim($dataLine);
            }
        }

        if ($this->jsonLength <= 0) {
            $this->sendJson();
        }
  }

  private function sendJson() {
      if ($this->json && $this->fifoFile) {
          fwrite($this->fifoFile, $this->json . "\n");
      }
      $this->json = '';
      $this->jsonLength = 0;
  }

  public function close() {
    fclose($this->handle);

    if ($this->fifoFile) {
        fclose($this->fifoFile);
    }
  }
}
