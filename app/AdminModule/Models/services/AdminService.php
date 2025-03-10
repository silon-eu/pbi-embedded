<?php


namespace App\AdminModule\Models\Service;

use Nette\Database\Connection;
use Tracy\Debugger;

class AdminService extends BaseService
{
    protected $database;

    public function __construct(Connection $database)
    {
        $this->database = $database;
    }

    public function getUsers() {
        return $this->database->query('
            select
                usr.id as id,
                usr.username as username,
                usr.name as name,
                usr.surname as surname,
                usr.groups_id as groups_id,
                grp.name as group_name
            from
                digiarchiv.users as usr
            join digiarchiv.groups as grp on
                usr.groups_id = grp.id;
        ')->fetchAll();
    }

    public function getGroups() {
        return $this->database->query('
            SELECT id, name
            FROM digiarchiv.groups
        ')->fetchPairs('id', 'name');
    }

    public function insertNewUser($values) {
        try {
            $this->database->query('
                INSERT INTO digiarchiv.users
                    (username, name, surname, groups_id)
                VALUES(\''.$values['username'].'\', \''.$values['name'].'\', \''.$values['surname'].'\', '.$values['group'].')
            ');
        } catch (\Throwable $th) {
            Debugger::barDump($th);
        }
    }
}