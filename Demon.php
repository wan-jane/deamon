<?php

/**
 * User: gengkang
 * Date: 16-7-19
 * Time: 下午3:41
 */
class Demon {
    /* config */
    const HOST = '127.0.0.1';
    const PORT = 6379;
    const MAXCONN = 2048;
    const pidfile = __CLASS__;
    const uid = 80;
    const gid = 80;

    protected $pool = NULL;
    protected $redis = NULL;

    public function __construct() {
        $this->pidfile = __DIR__ . '/' . self::pidfile . '.pid';
        $this->redis = new Redis();
    }

    private function daemon() {
        if (file_exists($this->pidfile)) {
            echo "The file $this->pidfile exists.\n";
            exit();
        }

        $pid = pcntl_fork();
        if ($pid == -1) {
            die('could not fork');
        } else if ($pid) {
            // we are the parent
            //pcntl_wait($status); //Protect against Zombie children
            exit($pid);
        } else {
            // we are the child
            file_put_contents($this->pidfile, getmypid());
            posix_setuid(self::uid);
            posix_setgid(self::gid);
            return (getmypid());
        }
    }

    private function run() {
        try {
            $this->redis->connect(self::HOST, self::PORT);
            $channel = array('news', 'login', 'logout');
            $this->redis->subscribe($channel, [$this, 'handle']);
        } catch (Exception $e) {
            print_r("异常：" . $e->getMessage());
            print_r("\n 重新链接频道.....\n");
            $this->run();
            print_r("\n 重新链接成功.....\n");
        }
    }

    private function start() {
        $pid = $this->daemon();
        $this->run();
    }

    private function onestart() {
        $this->run();
    }

    private function stop() {
        if (file_exists($this->pidfile)) {
            $pid = file_get_contents($this->pidfile);
            posix_kill($pid, 9);
            unlink($this->pidfile);
        }
    }

    private function help($proc) {
        printf("%s start | stop | help \n", $proc);
    }

    public function main($argv) {
        if (count($argv) < 2) {
            printf("please input help parameter\n");
            exit();
        }
        if ($argv[1] === 'stop') {
            $this->stop();
        } else if ($argv[1] === 'start') {
            $this->start();
        //} else if ($argv[1] === 'onestart') {
            //$this->onestart();
        } else {
            $this->help($argv[0]);
        }
    }

    public function handle($instance, $channelName, $message) {
        file_put_contents(__DIR__ . "/$channelName.txt", $message . "\n", FILE_APPEND);
    }
}

$example = new Demon();
$example->main($argv);
