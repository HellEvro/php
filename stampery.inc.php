<?php
require './vendor/autoload.php';

class Stampery
{

  private $endpoints = array(
    'prod' => 'https://api.stampery.com/v2',
    'beta' => 'https://stampery-api-beta.herokuapp.com/v2'
  );

  public function __construct($secret, $branch = 'prod')
  {
    $this->secret = $secret;
    $this->clientId = substr(md5($this->secret), 0, 15);
    $this->auth = base64_encode($this->clientId . ':' . $this->secret);
    $this->endpoint = isset($this->endpoints[$branch]) ? $this->endpoints[$branch] : $this->endpoints['prod'];
  }

  public function hash($data)
  {
    $type = gettype($data);
    if ($type == "resource")
    {
      $uri = stream_get_meta_data($data)["uri"];
      return hash_file('sha256', $uri);
    } else if ($type == "string")
    {
      return hash('sha256', $data);
    } else
    {
      $json4 = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
      $json2 = preg_replace('/^(  +?)\\1(?=[^ ])/m', '$1', $json4);
      return hash('sha256', $json2);
    }
  }

  private function _curl($method, $url, $query = null, $isJSON = false)
  {
    $headers = array('Authorization: ' . $this->auth);
    if ($isJSON)
      array_push($headers, 'Content-Type: application/json');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if (isset($query))
      curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

    $raw = curl_exec($ch);
    curl_close($ch);
    $res = json_decode($raw);
    if ($res)
    {
      if (isset($res->err))
        throw new Exception($res->err);
      else
        return $res;
    }
    else
      return $raw;
  }

  private function _get($action, $params = null)
  {
    $url = $this->endpoint . '/' . $action;
    if ($params)
      $url .= '?' . http_build_query($params);
    return $this->_curl('GET', $url);
  }

  private function _post($action, $params = null, $isJSON = false)
  {
    $url = $this->endpoint . '/' . $action;
    if ($isJSON)
      $query = json_encode($params);
    else
      $query = http_build_query($params);
    return $this->_curl('POST', $url, $query, $isJSON);
  }

  private function _stampJSON($data)
  {
    return $this->_post('stamps', $data, true);
  }

  private function _stampFile($data, $file)
  {
    $data['hash'] = $this->hash($file);
    return $this->_post('stamps', $data, true);
  }

  private function _checkDataIntegrity($proofHash, $data)
  {
    $dataHash = $this->hash($data);
    $stamp = $this->get($proofHash);
    $stampDataHash = $this->hash($stamp->data);
    return $dataHash == $stampDataHash;
  }

  private function _checkFileIntegrity($proofHash, $data, $file)
  {
    $data['hash'] = $this->hash($file);
    $dataHash = $this->hash($data);
    $stamp = $this->get($proofHash);
    $stampDataHash = $this->hash($stamp->data);
    return $dataHash == $stampDataHash;
  }

  public function stamp($data = array(), $file = null)
  {
    return igorw\retry(3, function() use ($data, $file) {
      if ($file)
        $stamp = $this->_stampFile($data, $file);
      else
        $stamp = $this->_stampJSON($data);
      if (isset($stamp->hash))
        return $stamp->hash;
      else
        throw new Exception('Could not stamp: reason unknown.');
   });
  }

  public function get($hash)
  {
    return $this->_get('stamps/' . strtoupper($hash));
  }

  public function checkIntegrity($proofHash, $data = array(), $file = null)
  {
    if ($file)
      return $this->_checkFileIntegrity($proofHash, $data, $file);
    else
      return $this->_checkDataIntegrity($proofHash, $data);
  }

}
