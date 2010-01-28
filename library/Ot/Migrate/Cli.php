<?php
class Ot_Migrate_Cli
{
    public static function argsIsValid()
    {
        // Possible environments that you can choose from
        $possibleEnvironments = array(
            'production',
            'staging',
            'development',
            'nonproduction',
            'testing',
        );
        
        $possibleMethods = array(
            'up',
            'down',
            'latest',
            ''
        );
    }
    public static function getArgs()
    {
        return $argv;
    }
    
    public static function error($message)
    {
        echo "================================================\n"
           . "ERROR!\n"
           . "================================================\n"
           ;
           
        die($message);
    }
}