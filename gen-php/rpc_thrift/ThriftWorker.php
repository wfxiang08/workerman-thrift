<?php

// 只负责RPC的基础逻辑, 不负责和Yii框架等集成
class ThriftWorker {

  protected $processor;

  /**
   * ThriftWorker constructor.
   * @param $processor
   * @param string $address
   * @param int $pool_size
   * @param string $service
   */
  public function __construct($processor, $address, $pool_size = 1, $service = None) {
    $this->processor = $processor;
  }

  //
  public function run() {

  }
}