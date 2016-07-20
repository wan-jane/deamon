<?php


ini_set('default_socket_timeout', -1);

/**
 * Class Demon
 */
abstract class Task {
    /* config */

    private $process_name = 'php_task_';

    const uid = 80;

    const gid = 80;

    const PID_DIR = __DIR__ . '/';

    public $pidfile;

    public $pidname;

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
            $i = file_put_contents($this->pidfile, getmypid());
            if ($i === false) {
                exit("无法写入pid文件！");
            }
            posix_setuid(self::uid);
            posix_setgid(self::gid);
            cli_set_process_title($this->process_name . $this->pidname);
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
        printf("%s php your-class-name.php start|stop|restart|stat|list|help taskname\n", $proc);
        print <<<DOC
    使用方法：
    继承此类重写run方法，在重写时,在循环里面调用parent::run();
    指定pid文件的名字,用来管理stop|stat|list)进程,要求有意义并且唯一;
    最后： (new yourclass)->main(\$argv)来运行你的代码;
    php your-phpfile start task_name      :启动当前脚本并设置tsak_name
    php any-your-phpfile restart task_name:重新启动task_name
    php any-your-phpfile stop task_name   :停止 task_name
    php any-your-phpfile stat task_name   :输出进程号和进程名称task_name
    php any-your-phpfile list 任意参数     :列出正在执行的类名task_name

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

        if (isset($argv[2])) {
            $this->pidfile = self::PID_DIR . $argv[2] . ".pid";
            $this->pidname = $argv[2];
        }

        if ($argv[1] === 'stop') {
            $this->stop();
        } else if ($argv[1] === 'start') {
            $this->start();
        } else if ($argv[1] === 'list') {
            $this->list_pid();
        } else if ($argv[1] === 'restart') {
            $this->restart();
        } else if ($argv[1] === 'stat') {
            $this->stat();
        } else {
            $this->help("使用方法 :");
        }
    }

    private function stat() {
        if (is_file($this->pidfile)) {
            posix_kill(file_get_contents($this->pidfile), SIGHUP);
        } else {
            print "\n-------------指定进程没有启动-----------\n";
        }
    }

    /**
     * @param $signo
     */
    public function signoH($signo) {
        switch ($signo) {
            case SIGHUP :
                print "\n\e[32m------------运行状态------------\n";
                print "PID : " . file_get_contents($this->pidfile) . "\n";
                print "CLASS_NAME : " . $this->pidname . "\n";
                print "PROCESS_NAME : " . $this->process_name . $this->pidname . "\n";
                print "________________________________\n";
                print "\e[0m";
                break;
            case SIGTERM:
            default :
              ;
        }
    }

    private function list_pid() {
        print "runnig class list：\n";
        foreach (glob(self::PID_DIR."*.pid") as $_file) {
            $arr = explode("/", $_file);
            $pidfile = $arr[count($arr) - 1];
            $pidname = str_replace('.pid', '', $pidfile);
            print "\033[32m$pidname\n";
        }
        print "\033[0m";
    }
}
//useage:
class demoT extends Task {
    /**
     * @overwrite run 
     */
    protected function run() {
        while (true) {
            parent::run();
            //do something you want
            sleep(5);
        }
    }
}

(new demoT())->main($argv);

// php Demon.php start demoT
// php Demon.php stat demoT
// php Demon.php restart demoT
// php Demon.php stop demoT
// php Demon.php list anyparam
