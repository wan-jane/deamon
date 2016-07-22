<?php
/**
 * FILE_NAME : Signal.php
 * USER      : wawa
 * TIME      : 下午6:38
 * WHAT_TODO : todo
 */

final class Signal{
    public static $signo = 0;
    protected static $ini = null;
    public static function set($signo){
        self::$signo = $signo;
    }
    public static function get(){
        return(self::$signo);
    }
    public static function reset(){
        self::$signo = 0;
    }
}