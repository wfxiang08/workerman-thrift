<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Workerman\Lib;

use Workerman\Events\EventInterface;
use Exception;

/**
 * Timer.
 *
 * example:
 * Workerman\Lib\Timer::add($time_interval, callback, array($arg1, $arg2..));
 */
class Timer {
  /**
   * Tasks that based on ALARM signal.
   * [
   *   run_time => [[$func, $args, $persistent, time_interval],[$func, $args, $persistent, time_interval],..]],
   *   run_time => [[$func, $args, $persistent, time_interval],[$func, $args, $persistent, time_interval],..]],
   *   ..
   * ]
   *
   * @var array
   */
  protected static $_tasks = array();

  /**
   * event
   *
   * @var \Workerman\Events\EventInterface
   */
  protected static $_event = null;

  /**
   * Init.
   *
   * @param \Workerman\Events\EventInterface $event
   * @return void
   */
  public static function init($event = null) {
    // Timer的两种处理模型
    // 1. libevent
    // 2. 自定义的Timer
    //
    if ($event) {
      // 如果是event, 则会自己进行eventloop
      self::$_event = $event;
    } else {
      pcntl_signal(SIGALRM, array('\Workerman\Lib\Timer', 'signalHandle'), false);
    }
  }

  /**
   * ALARM signal handler.
   *
   * @return void
   */
  public static function signalHandle() {
    if (!self::$_event) {
      // 设置一个1s后的Alarm
      pcntl_alarm(1);
      // 处理当前时刻的事情
      self::tick();
    }
  }

  /**
   * Add a timer.
   *
   * @param int $time_interval
   * @param callback $func
   * @param mixed $args
   * @param bool $persistent
   * @return bool
   */
  public static function add($time_interval, $func, $args = array(), $persistent = true) {
    if ($time_interval <= 0) {
      echo new Exception("bad time_interval");
      return false;
    }

    if (self::$_event) {
      return self::$_event->add($time_interval,
        $persistent ? EventInterface::EV_TIMER : EventInterface::EV_TIMER_ONCE, $func, $args);
    } else {

      if (!is_callable($func)) {
        echo new Exception("not callable");
        return false;
      }

      // alarm的初始启动
      if (empty(self::$_tasks)) {
        pcntl_alarm(1);
      }

      // 记录某个时刻点的callbacks
      $time_now = time();
      $run_time = $time_now + $time_interval;
      if (!isset(self::$_tasks[$run_time])) {
        self::$_tasks[$run_time] = array();
      }
      self::$_tasks[$run_time][] = array($func, (array)$args, $persistent, $time_interval);
      return true;
    }
  }


  /**
   * Tick.
   *
   * @return void
   */
  public static function tick() {
    // 如果没有tasks, 则取消alarm
    if (empty(self::$_tasks)) {
      pcntl_alarm(0);
      return;
    }

    // 不断tick, 然后发现该执行了,则任务取出来执行
    $time_now = time();
    foreach (self::$_tasks as $run_time => $task_data) {
      if ($time_now >= $run_time) {
        foreach ($task_data as $index => $one_task) {
          $task_func = $one_task[0];
          $task_args = $one_task[1];
          $persistent = $one_task[2];
          $time_interval = $one_task[3];

          // 如何evoke函数呢?
          try {
            call_user_func_array($task_func, $task_args);
          } catch (\Exception $e) {
            echo $e;
          }

          // 是否继续
          if ($persistent) {
            self::add($time_interval, $task_func, $task_args);
          }
        }
        unset(self::$_tasks[$run_time]);
      }
    }
  }

  /**
   * Remove a timer.
   *
   * @param mixed $timer_id
   * @return bool
   */
  public static function del($timer_id) {
    if (self::$_event) {
      return self::$_event->del($timer_id, EventInterface::EV_TIMER);
    }

    return false;
  }

  /**
   * Remove all timers.
   *
   * @return void
   */
  public static function delAll() {
    self::$_tasks = array();
    pcntl_alarm(0);
    if (self::$_event) {
      self::$_event->clearAllTimer();
    }
  }
}
