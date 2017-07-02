<?php

require_once THRIFT_ROOT . '/Applications/ThriftRpc/Lib/Thrift/ClassLoader/ThriftClassLoader.php';

use Thrift\Exception\TApplicationException;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TFramedTransport;
use Thrift\Transport\TMemoryBuffer;
use Thrift\Transport\TSocket;
use Thrift\Type\TMessageType;


// 只负责RPC的基础逻辑, 不负责和Yii框架等集成
class ThriftWorker {

  const MESSAGE_TYPE_HEART_BEAT = 20;
  const MESSAGE_TYPE_STOP = 21;
  const MESSAGE_TYPE_STOP_CONFIRM = 22;

  protected $processor;
  /**
   * @var string
   */
  protected $host;
  protected $port;

  protected $service;

  protected $socket;

  protected $reconnect_interval = 1;
  protected $alive;

  protected $last_hb_time = 0;

  /**
   * ThriftWorker constructor.
   * @param $processor
   * @param string $address
   * @param int $pool_size
   * @param string $service
   */
  public function __construct($processor, $host, $port, $service = None) {
    $this->processor = $processor;
    $this->host = $host;
    $this->port = $port;
    $this->service = $service;
    $this->alive = true;
  }

  /**
   * @return bool
   */
  public function shouldStop() {
    return false;
  }

  public function run() {
    // 建立连接
    while ($this->alive) {
      $this->connectToLb();
    }

  }

  protected function connectToLb() {
    $socket = new TSocket($this->host, $this->port);

    try {
      $socket->open();
      // $socket->setRecvTimeout()
    } catch (\Exception $ex) {
      // 打开失败, 暂停
      sleep($this->reconnect_interval);
      if ($this->reconnect_interval <= 4) {
        $this->reconnect_interval *= 2;
      }
      return;
    }

    // 连接成功, 则正常工作
    $this->reconnect_interval = 1;
    $this->last_hb_time = time();

    $transport = new TFramedTransport($socket, true, true);
    $protocol = new TBinaryProtocol($transport);

    $name = "";
    $type = 0;
    $seqid = 0;
    while (true) {
      try {
        $protocol->readMessageBegin($name, $type, $seqid);
        if ($type == self::MESSAGE_TYPE_HEART_BEAT) {
          // 如果是心跳, 则立马返回
          $protocol->writeMessageBegin($name, $type, $seqid);
          $transport->flush();
          $this->last_hb_time = time();
        } else if ($type == self::MESSAGE_TYPE_STOP_CONFIRM) {
          // 准备关闭
          break;
        } else {
          // 临时的Buffer有助于处理数据序列化的异常, 保证异常发生时 $transport 中的数据是干净的
          $outputBuffer = new TMemoryBuffer();
          try {
            // 处理其他请求
            // $fname, $mtype, $rseqid
            $this->processor->process($protocol, $outputBuffer, $name, $type, $seqid);

          } catch (\Exception $ex) {
            $this->writeExceptionBack($ex, $name, $seqid, $protocol, $transport);
            continue;
          }

          // 正常请求的返回
          $transport->putBack($outputBuffer->getBuffer());
          $transport->flush();


        }
      } catch (\Exception $ex) {
        // 这里出现异常, 就必须断开重连了
        break;
      }
    }
  }

  /**
   * @param TBinaryProtocol $protocol
   * @param TFramedTransport $transport
   */
  protected function writeStopBack($protocol, $transport) {
    $protocol->writeMessageBegin("stop", self::MESSAGE_TYPE_STOP, 0);
    $protocol->writeMessageEnd();
    $transport->flush();
  }


  /**
   * @param \Exception $ex
   * @param string $name
   * @param $seqid
   * @param TBinaryProtocol $protocol
   * @param TFramedTransport $transport
   */
  protected function writeExceptionBack($ex, $name, $seqid, $protocol, $transport) {
    // TODO: 优化Trace
    $msg = $ex->getTraceAsString();

    $x = new TApplicationException(TApplicationException::INVALID_PROTOCOL, $msg);
    $protocol->writeMessageBegin($name, TMessageType::EXCEPTION, $seqid);
    $x->write($protocol);
    $protocol->writeMessageEnd();
    $transport->flush();
  }
}