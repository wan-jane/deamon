<?php

/**
 * FILE_NAME : Deamon.php
 * USER      : wawa
 * TIME      : 下午6:36
 * WHAT_TODO :
 */
class Daemon {
    /* config */
    const LISTEN = "tcp://192.168.2.15:5555";
    const pidfile = __CLASS__;
    const uid = 80;
    const gid = 80;
    const sleep = 5;

    protected $pool = NULL;
    protected $config = array();

    public function __construct($uid, $gid, $class) {
        $this->pidfile = __DIR__ . '/' . basename(get_class($class), '.php') . '.pid';
        //$this->config = parse_ini_file('sender.ini', true); //include_once(__DIR__."/config.php");
        $this->uid = $uid;
        $this->gid = $gid;
        $this->class = $class;
        $this->classname = get_class($class);

        $this->signal();
    }

    public function signal() {

        pcntl_signal(SIGHUP, function ($signo) /*use ()*/ {
            //echo "\n This signal is called. [$signo] \n";
            printf("The process has been reload.\n");
            Signal::set($signo);
        });

    }

    private function daemon() {
        if (file_exists($this->pidfile)) {
            echo "The file $this->pidfile exists.\n";
            exit();
        }
        cli_set_process_title($this->classname);
        $pid = pcntl_fork();
        if ($pid == -1) {
            die('could not fork');
        } elseif ($pid) {
            // we are the parent
            //pcntl_wait($status); //Protect against Zombie children
            exit($pid);
        } else {
            file_put_contents($this->pidfile, getmypid());
            posix_setuid(self::uid);
            posix_setgid(self::gid);
            return (getmypid());
        }
    }

    private function run() {

        while (true) {

            printf("The process begin.\n");
            $this->class->run();
            printf("The process end.\n");

        }
    }

    private function foreground() {
        $this->run();
    }

    private function start() {
        $pid = $this->daemon();
        for (; ;) {
            $this->run();
            sleep(self::sleep);
        }
    }

    private function stop() {

        if (file_exists($this->pidfile)) {
            $pid = file_get_contents($this->pidfile);
            posix_kill($pid, 9);
            unlink($this->pidfile);
        }
    }

    private function reload() {
        if (file_exists($this->pidfile)) {
            $pid = file_get_contents($this->pidfile);
            //posix_kill(posix_getpid(), SIGHUP);
            posix_kill($pid, SIGHUP);
        }
    }

    private function status() {
        if (file_exists($this->pidfile)) {
            $pid = file_get_contents($this->pidfile);
            system(sprintf("ps ax | grep %s | grep -v grep", $pid));
        }
    }

    private function help($proc) {
        printf("%s start | stop | restart | status | foreground | help \n", $proc);
    }

    public function main($argv) {

        if (count($argv) < 2) {
            $this->help($argv[0]);
            printf("please input help parameter\n");
            exit();
        }
        if ($argv[1] === 'stop') {
            $this->stop();
        } else if ($argv[1] === 'start') {
            $this->start();
        } else if ($argv[1] === 'restart') {
            $this->stop();
            $this->start();
        } else if ($argv[1] === 'status') {
            $this->status();
        } else if ($argv[1] === 'foreground') {
            $this->foreground();
        } else if ($argv[1] === 'reload') {
            $this->reload();
        } else {
            $this->help($argv[0]);
        }
    }
}