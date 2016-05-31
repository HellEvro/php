<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Epkm\MessagePackRpc\Client;
use bb\Sha3\Sha3;

class Stampery
{

  private $apiEndpoints = array(
    'prod' => array('api.stampery.com', 4000),
    'beta' => array('api-beta.stampery.com', 4000)
  );

  private $amqpEndpoints = array(
    'prod' => array('young-squirrel.rmq.cloudamqp.com', 5672, 'consumer', '9FBln3UxOgwgLZtYvResNXE7', 'ukgmnhoi'),
    'beta' => array('young-squirrel.rmq.cloudamqp.com', 5672, 'consumer', '9FBln3UxOgwgLZtYvResNXE7', 'beta')
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
    $this->amqpChannel->basic_consume($this->clientId.'-clnt', '', false, true, false, false, function($msg)
    {
      $hash = $msg->delivery_info["routing_key"];
      $proof = msgpack_unpack($msg->body);
      $this->_trigger('proof', array($hash, $proof));
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
    $this->apiEndpoint = isset($this->apiEndpoints[$branch]) ? $this->apiEndpoints[$branch] : $this->apiEndpoints['prod'];
    $this->amqpEndpoint = isset($this->amqpEndpoints[$branch]) ? $this->amqpEndpoints[$branch] : $this->amqpEndpoints['prod'];
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

}

