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
    return hash('sha256', json_encode($data));
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
    #curl_setopt($ch, CURLOPT_VERBOSE, true);

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
    $url = $this->endpoint . '/stamps';
    $eol = "\r\n";
    $mime_boundary = md5(time());
    $isResource = gettype($file) == "resource";
    $name = '';

    if ($isResource)
    {
      $name = basename(stream_get_meta_data($file)["uri"]);
      $string = stream_get_contents($file);
    }
    else
      $string = $file;

    if (isset($data->name))
      $name = $data->name;

    $mp = '--' . $mime_boundary . $eol;
    $mp .= 'Content-Disposition: form-data; name="data"' . $eol . $eol;
    $mp .= json_encode($data) . $eol;
    $mp .= '--' . $mime_boundary . $eol;
    $mp .= 'Content-Disposition: form-data; name="file"; filename="' . $name . '"' . $eol;
    $mp .= 'Content-Type: text/plain' . $eol . $eol;
    $mp .= chunk_split($string) . $eol;
    $mp .= '--' . $mime_boundary . '--' . $eol . $eol;

    $params = array('http' => array(
                      'method' => 'POST',
                      'header' => 'Content-Type: multipart/form-data; boundary=' . $mime_boundary . $eol .'Authorization: ' . $this->auth . $eol,
                      'content' => $mp
                   ));

    $ctx = stream_context_create($params);
    $raw = file_get_contents($url, FILE_TEXT, $ctx);
    $res = json_decode($raw);
    if (isset($res->hash))
      return $res;
    else if (explode(' ', $http_response_header[0])[1] == '409')
      throw new Exception('Could not stamp: file was already stamped.');
    else
      throw new Exception('Could not stamp: reason unknown.');
  }

  public function stamp($data = array(), $file = null)
  {
    igorw\retry(3, function() use ($data, $file) {
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
    return $this->_get('stamps/' . $hash)->stamp;
  }

}
