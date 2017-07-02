<?php

define('THRIFT_ROOT', __DIR__);
require_once THRIFT_ROOT . '/Applications/ThriftRpc/Lib/Thrift/ClassLoader/ThriftClassLoader.php';

use Thrift\ClassLoader\ThriftClassLoader;
use Thrift\Exception\TException;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Protocol\TMultiplexedProtocol;
use Thrift\Transport\TSocket;

$loader = new ThriftClassLoader();

// 参考: https://thrift.apache.org/tutorial/php
$loader->registerNamespace('Thrift', THRIFT_ROOT . '/Applications/ThriftRpc/Lib');
$loader->registerDefinition('geoip_service', THRIFT_ROOT . "/gen-php/");
$loader->registerDefinition('rpc_thrift', THRIFT_ROOT . "/gen-php/");

$loader->register();

class TestCode {
  function testDirectRPC() {
    try {
      $socket = new TSocket('localhost', 5563);
      // $transport = new TBufferedTransport($socket, 1024, 1024);
      $transport = new \Thrift\Transport\TFramedTransport($socket, true, true);
      $protocol = new TBinaryProtocol($transport);
      $client = new \geoip_service\GeoIpServiceClient($protocol);

      $transport->open();

      $client->ping();
      print "ping()\n";

      $data = $client->IpToGeoData("120.52.139.7");
      var_dump($data);

      $transport->close();

    } catch
    (TException $tx) {
      print 'TException: ' . $tx->getMessage() . "\n";
    }
  }


  function testProxiedRPC() {
    try {
      // 127.0.0.1:5550
      // /usr/local/rpc_proxy/proxy.sock

      // 直接使用rpc proxy进行通信
      // $socket = new TSocket('localhost', 5550);
      $socket = new TSocket('unix:///usr/local/rpc_proxy/proxy.sock');

      $service_name = "ipgeo";
      // $transport = new TBufferedTransport($socket, 1024, 1024);
      $transport = new \Thrift\Transport\TFramedTransport($socket, true, true);

      // 指定后端的服务
      $protocol = new TMultiplexedProtocol(new TBinaryProtocol($transport), $service_name);
      $client = new \geoip_service\GeoIpServiceClient($protocol);

      $transport->open();

      $client->ping();
      print "ping()\n";

      $data = $client->IpToGeoData("120.52.139.7");
      var_dump($data);

      $transport->close();

    } catch
    (TException $tx) {
      print 'TException: ' . $tx->getMessage() . "\n";
    }
  }
}

$testCode = new TestCode();
// $testCode->testDirectRPC();
$testCode->testProxiedRPC();
