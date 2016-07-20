<?php
/**
 * User: gk
 * Date: 16-7-19
 * Time: 下午3:41
 */
ini_set('default_socket_timeout', -1);

/**
 * Class Demon
 */
abstract class Demon {
    /* config */

    private $process_name = 'php_task_';

    const uid = 80;

    const gid = 80;

    public $pidfile;

    public $stop = false;

    /**
     * Demon constructor.
     */
    public function __construct() {
        $this->pidfile = __DIR__ . '/' . __CLASS__ . '.pid';
    }

    /**
     * @return int
     */
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
            exit($pid);
        } else {
            // we are the child
            file_put_contents($this->pidfile, getmypid());
            posix_setuid(self::uid);
            posix_setgid(self::gid);
            cli_set_process_title($this->process_name . __CLASS__);
            //pcntl_signal(SIGKILL, [$this, 'signoH']);
            pcntl_signal(SIGHUP, [$this, 'signoH']);
            pcntl_signal(SIGTERM, [$this, 'signoH']);
            pcntl_signal(SIGCHLD, [$this, 'signoH']);
            pcntl_signal(SIGQUIT, [$this, 'signoH']);
            pcntl_signal(SIGINT, [$this, 'signoH']);
            pcntl_signal(SIGUSR1, [$this, 'signoH']);
            return (getmypid());
        }
    }

    /**
     *
     */
    protected function run() {
        pcntl_signal_dispatch();
        if ($this->stop) {
            exit("进程退出\n");
        }
    }

    private function restart() {
        $this->stop();
        $this->start();
        print "重启成功！\n";
    }

    /**
     *
     */
    private function start() {
        $pid = $this->daemon();
        $this->run();
    }

    /**
     *
     */
    private function stop() {
        if (file_exists($this->pidfile)) {
            $pid = file_get_contents($this->pidfile);
            posix_kill($pid, SIGKILL);
            unlink($this->pidfile);
        }
    }

    /**
     * @param $proc
     */
    private function help($proc) {
        printf("%s php your-class-name.php start|stop|restart|stat|help  pidfileName\n", $proc);
        print <<<DOC
        使用：
        继承此类重写run方法，在重写时,在循环里面调用parent::run();
        指定pid文件的名字,用一个类去管理其他的类的进程,尽量有意义并且唯一;
        最后： (new yourclass)->main()来运行你的代码;

DOC;

    }

    /**
     * @param $argv
     */
    public function main($argv) {

        if (count($argv) < 3) {
            $this->help("使用方法 :");
            exit();
        }

        if(isset($argv[2])) {
            $this->pidfile = __DIR__ . '/' . $argv[2] . ".pid";
        }

        if ($argv[1] === 'stop') {
            $this->stop();
        } else if ($argv[1] === 'start') {
            $this->start();
        } else if ($argv[1] === 'restart') {
            $this->restart();
        } else if ($argv[1] === 'stat') {
            if (is_file($this->pidfile)) {
                posix_kill(file_get_contents($this->pidfile), SIGHUP);
            } else {
                print "\n-------------指定进程没有启动-----------\n";
            }
        } else {
            $this->help("使用方法 :");
        }
    }

    /**
     * @param $signo
     */
    public function signoH($signo) {
        switch ($signo) {
            case SIGHUP :
                print "\n------------运行状态------------\n";
                print "PID : " . file_get_contents($this->pidfile) . "\n";
                print "-----------------------------------\n";
                break;
            case SIGTERM:
                posix_kill(file_get_contents($this->pidfile), SIGKILL);
                break;
            default :
              ;
        }
    }
}
class demonT extends Demon {
    protected function run() {
        while (true) {
            parent::run();
            echo "hello \n";
            sleep(5);
        }
    }
}

(new demonT())->main($argv);
