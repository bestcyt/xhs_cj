<?php

namespace Mt\Lib;

class LogType extends \Fw\LogType
{
    const PHP_ERROR = 'php.error';

    const CRON_EXEC = 'cron.exec';
    const FATAL_ALERT_EMAIL = 'fatal_alert.email';
    const FATAL_ALERT_FEISHU = 'fatal_alert.feishu';

    const QUEUE_CHECK_ALIVE = 'queue.check_alive';
    const QUEUE_CHECK_USE = 'queue.check_use';
    const QUEUE_EXECUTE = 'queue.execute';
    const QUEUE_SHUTDOWN_NOT_SAFE = 'queue.shutdown_is_not_safe';
    const QUEUE_SHUTDOWN_SAFE = 'queue.shutdown_is_safe';
    const QUEUE_DISPATCH = 'queue.dispatch_await_tasks_count';

    const LOCK_FAIL = 'lock.fail';

    const API_RETURN_ERROR = 'api_return_error';
    const API_RETURN_OUTPUT = 'api_return_output';
}