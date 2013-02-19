<?php
class Ot_Cli_Output
{    
    public static function error($message)
    {        
        $string = "\n\n"
           . "================================================\n"
           . "ERROR!\n"
           . "================================================\n\n"
           . $message
           . "\n\n"
           ;
        
        file_put_contents('php://stderr', $string);
        
        exit;
        
    }
    
    public static function success($message)
    {
        
        $string = "\n\n"
           . "================================================\n"
           . "SUCCESS!\n"
           . "================================================\n\n"
           . (is_array($message) ? implode($message, "\n") : $message) 
           . "\n\n"
           ;
           
        echo $string;
        
        exit;
    }    
}