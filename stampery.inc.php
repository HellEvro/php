<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Epkm\MessagePackRpc\Client;
use bb\Sha3\Sha3;

class Anchor
{
  function __construct($protoAnchor)
  {
    $this->chain = $protoAnchor[0];
    $this->txId = $protoAnchor[1];
  }
}

class Proof
{
  function __construct($hash, $protoProof) {
    $this->hash = $hash;
    $this->version = $protoProof[0];
    $this->siblings = $protoProof[1] == "" ? array() : $protoProof[1];
    $this->root = $protoProof[2];
    $this->anchor = new Anchor($protoProof[3]);
  }
}

class Stampery
{
  private $apiEndpoints = array(
    'prod' => array('api.stampery.com', 4000),
    'beta' => array('api-beta.stampery.com', 4000)
  );

  private $amqpEndpoints = array(
    'prod' => array('young-squirrel.rmq.cloudamqp.com', 5672, 'consumer',
                    '9FBln3UxOgwgLZtYvResNXE7', 'ukgmnhoi'),
    'beta' => array('young-squirrel.rmq.cloudamqp.com', 5672, 'consumer',
                    '9FBln3UxOgwgLZtYvResNXE7', 'beta')
  );

  private function _apiLogin($endpoint)
  {
    $this->apiClient = new Client($endpoint[0], $endpoint[1]);
    $auth = $this->apiClient->call('stampery.3.auth', array($this->clientId, $this->clientSecret));
  }

  private function _amqpLogin($endpoint)
  {
    $this->amqpConn = call_user_func_array(
      function ($host, $port, $user, $pass, $vhost)
      {
        return new AMQPStreamConnection($host, $port, $user, $pass, $vhost);
      },
      $this->amqpEndpoint
    );
    $this->amqpChannel = $this->amqpConn->channel();
    $this->amqpChannel->basic_consume($this->clientId.'-clnt', '', false, true,
      false, false, function($msg)
    {
      $hash = $msg->delivery_info["routing_key"];
      $protoProof = msgpack_unpack($msg->body);
      $proof = new Proof($hash, $protoProof);
      $this->_trigger('proof', array($hash, $proof, $this));
    });
    $this->_trigger('ready', array($this));
    while(count($this->amqpChannel->callbacks)) {
      $this->amqpChannel->wait();
    }
  }

  public function __construct($secret, $branch = 'prod')
  {
    $this->eventHandlers = array();

    $this->clientSecret = $secret;
    $this->clientId = substr(md5($this->clientSecret), 0, 15);
    $this->apiEndpoint = isset($this->apiEndpoints[$branch])
      ? $this->apiEndpoints[$branch]
      : $this->apiEndpoints['prod'];
    $this->amqpEndpoint = isset($this->amqpEndpoints[$branch])
      ? $this->amqpEndpoints[$branch]
      : $this->amqpEndpoints['prod'];
  }

  public function start() {
    $this->_apiLogin($this->apiEndpoint);
    $this->_amqpLogin($this->amqpEndpoint);
  }

  private function _trigger($eventType, $params = array())
  {
    if (isset($this->eventHandlers[$eventType]))
      foreach ($this->eventHandlers[$eventType] as $callback)
        call_user_func_array($callback, $params);
  }

  public function on($eventType, $callback)
  {
    if (is_callable($callback))
    {
      if (isset($this->eventHandlers[$eventType]))
        array_push($this->eventHandlers[$eventType], $callback);
      else
        $this->eventHandlers[$eventType] = array($callback);
      return true;
    }
    else
      throw new Exception('Event callback is not callable.');
  }

  public function stamp($hash)
  {
    $hash = strtoupper($hash);
    $this->apiClient->call('stamp', array($hash));
    return true;
  }

  public function hash($data)
  {
    return strtoupper(Sha3::hash($data, 512));
  }

  public function prove($proof)
  {
    return $this->_prove(
      $proof->hash,
      array_reverse($proof->siblings),
      $proof->root
    );
  }

  private function _prove($hash, $siblings, $root)
  {
    if (count($siblings) > 0)
    {
      $head = array_pop($siblings);
      $mixed = $this->_mix($hash, $head);
      return $this->_prove($mixed, $siblings, $root);
    }
    else
      return $hash == $root;
  }

  private function _mix($a, $b)
  {
    $a = hex2bin($a);
    $b = hex2bin($b);
    $commuted = $a > $b ? $a.$b : $b.$a;
    $digest = $this->hash($commuted);
    return $digest;
  }
}

