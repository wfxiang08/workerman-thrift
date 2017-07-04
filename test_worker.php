<?php

define('THRIFT_ROOT', __DIR__);
require_once THRIFT_ROOT . '/Applications/ThriftRpc/Lib/Thrift/ClassLoader/ThriftClassLoader.php';

use Thrift\ClassLoader\ThriftClassLoader;

$loader = new ThriftClassLoader();

// 参考: https://thrift.apache.org/tutorial/php
$loader->registerNamespace('Thrift', THRIFT_ROOT . '/Applications/ThriftRpc/Lib');
$loader->registerDefinition('geoip_service', THRIFT_ROOT . "/gen-php/");
$loader->registerDefinition('rpc_thrift', THRIFT_ROOT . "/gen-php/");
$loader->registerDefinition('Services', THRIFT_ROOT . '/Applications/ThriftRpc/');

$loader->register();

class TestCode {
  function testWorker() {
    // echo "dir: " . __DIR__ . "\n";
    foreach (glob(__DIR__ . '/Applications/ThriftRpc/Services/HelloWorld/*.php') as $start_file) {
      // echo $start_file;
      require_once $start_file;
    }

    require_once __DIR__ . '/Applications/ThriftRpc/SMThriftWorker.php';

    // 创建Handler
    $handler = new \Services\HelloWorld\HelloWorldHandler();

    // 创建Processor
    $processor = new \Services\HelloWorld\HelloWorldProcessor($handler);

    // echo "Name: " . $handler->sayHello("wangfei") . "\n";
    $client = new SMThriftWorker($processor, 'tcp://localhost', 5556);
    $client->run();
  }


}

$testCode = new TestCode();
$testCode->testWorker();
