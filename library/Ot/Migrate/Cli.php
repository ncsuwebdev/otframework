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
    
    public static function status($message)
    {
        
        echo "\n\n"
           . "================================================\n"
           . "Migrations Complete!\n"
           . "================================================\n\n"
           . (is_array($message) ? implode($message, "\n") : $message) 
           . "\n\n"
           ;
           
        exit;
    }
}