<?php
namespace Services\HelloWorld;

/**
 * Autogenerated by Thrift Compiler (0.9.1)
 *
 * DO NOT EDIT UNLESS YOU ARE SURE THAT YOU KNOW WHAT YOU ARE DOING
 * @generated
 */
use Thrift\Type\TType;
use Thrift\Type\TMessageType;
use Thrift\Protocol\TBinaryProtocolAccelerated;
use Thrift\Exception\TApplicationException;
// 定义Thrift接口(自动生成)
// 注意:
//     sayHello
//     vs. send_sayHello
//         recv_sayHello
//     这里就是异步和同步的区别, 另外一个就是后端队列服务, 请求扔过去就不管了
//
interface HelloWorldIf {

  public function sayHello($name);
}

// ThriftClient
class HelloWorldClient implements HelloWorldIf {

  protected $input_ = null;

  protected $output_ = null;

  protected $seqid_ = 0;

  public function __construct($input, $output = null) {
    $this->input_ = $input;
    $this->output_ = $output ? $output : $input;
  }

  public function sayHello($name) {
    $this->send_sayHello($name);
    return $this->recv_sayHello();
  }

  public function send_sayHello($name) {
    $args = new HelloWorld_sayHello_args();
    $args->name = $name;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel) {
      thrift_protocol_write_binary($this->output_, 'sayHello', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    } else {
      $this->output_->writeMessageBegin('sayHello', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_sayHello() {
    $bin_accel = ($this->input_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary');
    // 二进制加速
    // 序列化时间?
    // 参考: http://python.jobbole.com/87559/
    // php7似乎比python快很多, 暂时不考虑优化
    //
    if ($bin_accel)
      $result = thrift_protocol_read_binary($this->input_, 'HelloWorld_sayHello_result', $this->input_->isStrictRead());
    else {
      $rseqid = 0;
      $fname = null;
      $mtype = 0;

      $this->input_->readMessageBegin($fname, $mtype, $rseqid);
      if ($mtype == TMessageType::EXCEPTION) {
        $x = new TApplicationException();
        $x->read($this->input_);
        $this->input_->readMessageEnd();
        throw $x;
      }
      $result = new HelloWorld_sayHello_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    throw new \Exception("sayHello failed: unknown result");
  }
}

// HELPER FUNCTIONS AND STRUCTURES
class HelloWorld_sayHello_args {

  static $_TSPEC;

  public $name = null;

  public function __construct($vals = null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'name',
          'type' => TType::STRING
        )
      );
    }
    if (is_array($vals)) {
      if (isset($vals['name'])) {
        $this->name = $vals['name'];
      }
    }
  }

  public function getName() {
    return 'HelloWorld_sayHello_args';
  }

  public function read($input) {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true) {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid) {
        case 1:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->name);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('HelloWorld_sayHello_args');
    if ($this->name !== null) {
      $xfer += $output->writeFieldBegin('name', TType::STRING, 1);
      $xfer += $output->writeString($this->name);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }
}

class HelloWorld_sayHello_result {

  static $_TSPEC;

  public $success = null;

  public function __construct($vals = null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        0 => array(
          'var' => 'success',
          'type' => TType::STRING
        )
      );
    }
    if (is_array($vals)) {
      if (isset($vals['success'])) {
        $this->success = $vals['success'];
      }
    }
  }

  public function getName() {
    return 'HelloWorld_sayHello_result';
  }

  public function read($input) {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true) {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid) {
        case 0:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->success);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('HelloWorld_sayHello_result');
    if ($this->success !== null) {
      $xfer += $output->writeFieldBegin('success', TType::STRING, 0);
      $xfer += $output->writeString($this->success);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }
}

// 服务器端的封装
class HelloWorldProcessor {

  protected $handler_ = null;

  public function __construct($handler) {
    $this->handler_ = $handler;
  }

  // 如何处理Process呢?
  public function process($input, $output) {
    $rseqid = 0;
    $fname = null;
    $mtype = 0;
    // 读取MessageBegin
    $input->readMessageBegin($fname, $mtype, $rseqid);
    // 得到方法名, 参数
    $methodname = 'process_' . $fname;
    if (!method_exists($this, $methodname)) {
      $input->skip(TType::STRUCT);
      $input->readMessageEnd();
      $x = new TApplicationException('Function ' . $fname . ' not implemented.', TApplicationException::UNKNOWN_METHOD);
      $output->writeMessageBegin($fname, TMessageType::EXCEPTION, $rseqid);
      $x->write($output);
      $output->writeMessageEnd();
      $output->getTransport()->flush();
      return;
    }

    // 调用具体的方法
    $this->$methodname($rseqid, $input, $output);
    return true;
  }

  protected function process_sayHello($seqid, $input, $output) {
    $args = new HelloWorld_sayHello_args();
    $args->read($input);
    $input->readMessageEnd();
    $result = new HelloWorld_sayHello_result();
    $result->success = $this->handler_->sayHello($args->name);
    $bin_accel = ($output instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel) {
      thrift_protocol_write_binary($output, 'sayHello', TMessageType::REPLY, $result, $seqid, $output->isStrictWrite());
    } else {
      $output->writeMessageBegin('sayHello', TMessageType::REPLY, $seqid);
      $result->write($output);
      $output->writeMessageEnd();
      $output->getTransport()->flush();
    }
  }
}
