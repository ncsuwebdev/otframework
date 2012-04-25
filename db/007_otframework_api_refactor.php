<?php

/**
 * Changes the how the API works
 */

class Db_007_otframework_api_refactor extends Ot_Migrate_Migration_Abstract
{
    public function up($dba)
    {
        $query = "
            CREATE TABLE IF NOT EXISTS  `" . $this->tablePrefix ."tbl_ot_api_app` (
            `appId` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
            `name` VARCHAR( 128 ) NOT NULL DEFAULT  '',
            `imageId` INT( 10 ) UNSIGNED NOT NULL DEFAULT  '0',
            `description` TEXT NOT NULL ,
            `website` VARCHAR( 255 ) NOT NULL DEFAULT  '',
            `accountId` INT( 10 ) UNSIGNED NOT NULL DEFAULT  '0',
            `apiKey` VARCHAR( 255 ) NOT NULL DEFAULT  '',
            PRIMARY KEY (  `appId` )
            ) ENGINE = INNODB DEFAULT CHARSET = utf8 AUTO_INCREMENT =3;
        ";
        $dba->query($query);
    }
    
    public function down($dba)
    {
    }
   
}