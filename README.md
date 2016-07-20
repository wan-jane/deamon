# Demon

        使用方法：
        继承此类重写run方法，在重写时,在循环里面调用parent::run();
        指定pid文件的名字,用一个类去管理其他的类的进程,尽量有意义并且唯一;
        最后： (new yourclass)->main($argv)来运行你的代码;
        php your-phpfile start task_name      :启动当前脚本并设置tsak_name
        php any-your-phpfile restart task_name:重新启动task_name
        php any-your-phpfile stop task_name   :停止 task_name
        php any-your-phpfile stat task_name   :输出进程号和进程名称task_name
        php any-your-phpfile list 任意参数    :列出正在执行的类名task_name
