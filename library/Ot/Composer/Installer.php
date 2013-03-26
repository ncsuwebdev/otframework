<?php
use Composer\Script\Event;

/**
 * Installer to be used when composer is installing the base app.
 */
class Ot_Composer_Installer
{
    public static function setupSymlinks(Event $event)
    {
        $paths = self::_helperDirectory($event);

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
            @unlink($paths['basePath'] . $f);
            symlink($paths['otfVendorPath'] . $f, $paths['basePath'] . $f);

            $io->write('Symlinking ' . $f);
        }

        // link ncsubootstrap into css

        @unlink($paths['basePath'] . '/public/css/ncsubootstrap');
        symlink($paths['ncsubootstrapVendorPath'], $paths['basePath'] . '/public/css/ncsubootstrap');
    }

    public static function setupWritableDirectories(Event $event)
    {
        $paths = self::_helperDirectory($event);

        $io = $event->getIO();

        $writable = array(
            '/cache',
            '/overrides',
        );

        foreach ($writable as $w) {
            self::_helperRecursiveChmod($paths['basePath'] . $w, 0757);
            $io->write('Making ' . $w . ' writable.');
        }
    }

    public static function copyApplicationIni(Event $event)
    {
        $paths = self::_helperDirectory($event);

        $io = $event->getIO();

        if (!is_file($paths['basePath'] . '/application/configs/application.ini')) {
            copy($paths['basePath'] . '/application/configs/application.default.ini', $paths['basePath'] . '/application/configs/application.ini');

            $io->write('application.default.ini copied to application.ini');
        } else {
            $io->write('application.ini already exists...skipping');
        }

    }

    public static function copyDbMigrations(Event $event)
    {
        $paths = self::_helperDirectory($event);

        $io = $event->getIO();

        self::_helperRecursiveCopy($paths['otfVendorPath'] . '/db', $paths['basePath'] . '/db');
        $io->write('db migration files copied');
    }

    public static function _helperDirectory(Event $event)
    {
        return array(
            'basePath'                => realpath($event->getComposer()->getConfig()->get('vendor-dir') . '/../'),
            'otfVendorPath'           => realpath($event->getComposer()->getConfig()->get('vendor-dir') . '/ncsuwebdev/otframework'),
            'ncsubootstrapVendorPath' => realpath($event->getComposer()->getConfig()->get('vendor-dir') . '/ncsuwebdev/ncsubootstrap'),
        );
    }

    public static function _helperRecursiveChmod($path, $permission)
    {
        // Check if the path exists
        if (!file_exists($path)) {
            return(false);
        }

        // See whether this is a file
        if (is_file($path)) {

            // Chmod the file with our given filepermissions
            chmod($path, $permission);

        // If this is a directory...
        } elseif (is_dir($path)) {

            // Then get an array of the contents
            $foldersAndFiles = scandir($path);

            // Remove "." and ".." from the list
            $entries = array_slice($foldersAndFiles, 2);

            // Parse every result...
            foreach ($entries as $entry) {

                // And call this function again recursively, with the same permissions
                self::_helperRecursiveChmod($path . "/" . $entry, $permission);

            }

            // When we are done with the contents of the directory, we chmod the directory itself
            chmod($path, $permission);
        }

        // Everything seemed to work out well, return TRUE
        return(true);
    }

    public static function _helperRecursiveCopy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);

        while (false !== ($file = readdir($dir))) {

            if (($file != '.') && ($file != '..')) {

                if (is_dir($src . '/' . $file)) {
                    self::_helperRecursiveCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }

        closedir($dir);
    }
}