<?php
class Ot_Cronjob_EmailQueue implements Ot_Cron_JobInterface
{
    public function execute($lastRunDt = null)
    {
        $eq = new Ot_Model_DbTable_EmailQueue();

        $messages = $eq->getWaitingEmails(20);

        $logger = Zend_Registry::get('logger');

        foreach ($messages as $m) {
            try {
                $m['zendMailObject']->send();

                $m['status'] = 'sent';
                $m['sentDt'] = time();

            } catch (Exception $e) {
                $m['status'] = 'error';
            }

            $where = $eq->getAdapter()->quoteInto('queueId = ?', $m['queueId']);

            $eq->update($m, $where);

            $logOptions = array(
                    'attributeName' => 'queueId',
                    'attributeId'   => $m['queueId'],
            );

            $logger->setEventItem('attributeName', 'queueId');
            $logger->setEventItem('attributeId', $m['queueId']);

            $logger->log('Mail Sent', Zend_Log::INFO);
        }
    }
}