<?php
use Composer\Script\Event;

class Ot_Composer_Installer
{
    public static function symlinks(Event $event)
    {
        $composer = $event->getComposer();
        
        $io = $event->getIO();
        
        $io->write(print_R($composer, true));
    }
}