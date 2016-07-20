# Demon
the php demon simple
this is a simple of php demon,can used in background works
 使用：
        继承此类重写run方法，在重写时,在循环里面调用parent::run();
        指定pid文件的名字,用一个类去管理其他的类的进程, pidfilename尽量有意义并且唯一;
        最后： (new yourclass)->main()来运行你的代码;
        ex:
           php demon.php start pidfilename
