<?php
class Ot_Migrate_Cli
{    
    public static function error($message)
    {
        echo "\n\n"
           . "================================================\n"
           . "ERROR!\n"
           . "================================================\n\n"
           . $message
           . "\n\n"
           ;
           
        exit;
    }
}