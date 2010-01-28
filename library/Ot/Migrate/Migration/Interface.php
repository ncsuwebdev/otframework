<?php
interface Ot_Migrate_Migration_Interface
{
    public function up($pdo);
    
    public function down($pdo);
}