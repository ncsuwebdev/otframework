<?php
interface Ot_Migrate_Migration_Interface
{
    public function up($dba);
    
    public function down($dba);
}