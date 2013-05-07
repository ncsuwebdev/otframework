<?php

define('LOCK_DIR', APPLICATION_PATH . '/../cache/');  
define('LOCK_SUFFIX', '.lock');  

class Ot_Cron_Lock {  

    private static $pid;  

    function __construct() {}  

    function __clone() {}  

    private static function isRunning()
    {  

        $pids = explode(PHP_EOL, `ps -e | awk '{print $1}'`);  
        
        if (in_array(self::$pid, $pids)) {
            return TRUE;
        }
        
        return FALSE;
    }  

    public static function lock($jobName)
    {

        $lockFile = LOCK_DIR . $jobName . LOCK_SUFFIX;

        if (file_exists($lockFile)) {  
            
            // Is running?  
            self::$pid = file_get_contents($lockFile);  
            if(self::isRunning()) {  
                error_log("==".self::$pid."== Already in progress...");  
                return FALSE;  
            }  
            else {
                error_log("==".self::$pid."== Previous job died abruptly...");  
            }
        }  

        self::$pid = getmypid();  
        file_put_contents($lockFile, self::$pid);  
        error_log("==".self::$pid."== Lock acquired, processing the job...");  
        return self::$pid;  
    }  

    public static function unlock($jobName)
    {  

        $lockFile = LOCK_DIR . $jobName . LOCK_SUFFIX;  

        if (file_exists($lockFile)) {
            unlink($lockFile);  
        }

        error_log("==".self::$pid."== Releasing lock...");  
        return TRUE;  
    }  

}  