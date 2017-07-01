<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
use Workerman\Worker;

require_once __DIR__ . '/ThriftWorker.php';


// ThriftWorker如何工作呢?
$worker = new ThriftWorker('tcp://0.0.0.0:9090');
$worker->count = 16;
$worker->class = 'HelloWorld';


// 如果不是在根目录启动，则运行runAll方法
// 两种运行方法, 被单独被启动; 整体被启动
// runAll好处? 运维上比较方便 所有的服务都在一个进程内部统一启动
if (!defined('GLOBAL_START')) {
  Worker::runAll();
}
