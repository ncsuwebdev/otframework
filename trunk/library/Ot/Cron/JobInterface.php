<?php
interface Ot_Cron_JobInterface
{
    public function execute($lastRunDt = null);
}
