<?php


namespace App\AdminModule\Models\Service;

use Nette\Database\Connection;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

class ReportingService extends BaseService
{
    protected $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function getPermissions(): Selection
    {
        return $this->database->table('rep_pages_permissions');
    }

    public function getTabs(): Selection
    {
        return $this->database->table('rep_tabs');
    }

    public function getTiles(): Selection
    {
        return $this->database->table('rep_tiles');
    }

}