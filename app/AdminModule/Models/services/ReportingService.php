<?php


namespace App\AdminModule\Models\Service;

use Nette\Database\Connection;
use Nette\Database\Explorer;
use Nette\Database\ResultSet;
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

    public function getPermissions(): ResultSet
    {
        return $this->database->query('select * from rep_permissions_matrix');
    }

    public function getGroupPermissions(int $groupId): ResultSet
    {
        return $this->database->query('select distinct tab_id,tab_name,tile_id,tile_name,page_id,page_name from rep_permissions_matrix where group_id = ?', $groupId);
    }

    public function getUserPermissions(int $userId): ResultSet
    {
        return $this->database->query('select * from rep_permissions_matrix where user_id = ? or group_user_id = ?', $userId, $userId);
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