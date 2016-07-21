<?php
/**
 * User: gengkang
 * Date: 16-7-20
 * Time: 下午2:51
 */
require_once 'Task.php';
class OrderSubscribeTask extends Task {

    protected function run() {

        while (true) {
            parent::run();
            file_put_contents("Order.txt", date('Y-m-d H:i:s'), FILE_APPEND);
            sleep(10);
        }
    }
}

(new OrderSubscribeTask())->main($argv);