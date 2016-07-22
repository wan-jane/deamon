<?php
/**
 * FILE_NAME : Test.php
 * USER      : wawa
 * TIME      : 下午6:37
 * WHAT_TODO :
 */

require_once 'Deamon.php';
require_once 'Signal.php';
class Test {
    //public static $signal = null;

    public function __construct() {
        //self::$signal == null;
    }
    public function run(){
        while(true){
            pcntl_signal_dispatch();
            printf(".");
            sleep(1);
            if(Signal::get() == SIGHUP){
                Signal::reset();
                break;
            }
        }
        printf("\n");
    }
}

$daemon = new Daemon(80,80, new Test());
$daemon->main($argv);