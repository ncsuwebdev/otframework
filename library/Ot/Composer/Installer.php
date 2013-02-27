<?php
use Composer\Script\Event;

/**
 * Installer to be used when composer is installing the base app.
 */
class Ot_Composer_Installer
{
    public static function install(Event $event)
    {
        $basePath = realpath($event->getComposer()->getConfig()->get('vendor-dir') . '/../');
        $otfVendorPath = realpath($event->getComposer()->getConfig()->get('vendor-dir') . '/ncsuwebdev/otframework');
                
        $io = $event->getIO();
        
        $foldersToLink = array(
            '/application/languages/ot',
            '/application/modules/ot',
            '/public/scripts/ot',
            '/public/css/ot',
            '/public/images/ot',
            '/public/themes/ot',
            '/public/min',
            '/otutils',
        );

        foreach ($foldersToLink as $f) {
            exec('rm ' . $basePath . $f);
            exec('ln -s ' . $otfVendorPath . $f . ' ' . $basePath . $f);
            $io->write('Symlinking ' . $f);
        }

        $writable = array(
            '/cache',
            '/overrides',
        );

        foreach ($writable as $w) {
            exec('chmod -R 757 ' . $basePath . $w);
            $io->write('Making ' . $w . ' writable.');
        }

    }
}