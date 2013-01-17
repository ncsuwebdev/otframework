<?php
class Ot_Cli_Output
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
    
    public static function success($message)
    {
        
        echo "\n\n"
           . "================================================\n"
           . "SUCCESS!\n"
           . "================================================\n\n"
           . (is_array($message) ? implode($message, "\n") : $message) 
           . "\n\n"
           ;
           
        exit;
    }    
}