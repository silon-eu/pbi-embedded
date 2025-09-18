<?php

namespace App\ReportingModule\Models\Service;

use Nette\Database\Explorer;
use Nette\Database\Table\Selection;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use Tracy\ILogger;

class AccessLogService extends BaseService
{
    public function __construct(
        protected Explorer $database,
        protected \Nette\Caching\Cache $cache
    ) {

    }

    public function getAccessLog(): Selection
    {
        return $this->database->table('rep_access_log');
    }

    public function logAccess(?int $tabId = null, ?int $tileId = null, ?int $pageId = null, ?int $userId = null): void
    {
        $this->database->table('rep_access_log')->insert([
            'rep_tabs_id' => $tabId,
            'rep_tiles_id' => $tileId,
            'rep_pages_id' => $pageId,
            'users_id' => $userId,
        ]);
    }

    public function cleanUp(): void
    {
        try {
            $this->database->query('DELETE FROM rep_access_log WHERE created_at < NOW() - INTERVAL 370 DAY');
        } catch (\Exception $e) {
            Debugger::log($e, ILogger::ERROR);
        }
    }

}