<?php


namespace App\AdminModule\Models\Service;

use Nette\Database\Connection;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

class UsersService extends BaseService
{
    protected $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function getDatasource()
    {
        return $this->database->table('users');
    }

    public function getUser(int $id): ?ActiveRow
    {
        return $this->database->table('users')->get($id);
    }

    public function createUser(array|ArrayHash $values): ?ActiveRow
    {
        return $this->database->table('users')->insert($values);
    }

    public function updateUser(int $id, array|ArrayHash $values): ?bool
    {
        return $this->database->table('users')->get($id)?->update($values);
    }

    public function deleteUser(int $id): ?int
    {
        return $this->database->table('users')->get($id)?->delete();
    }

    public function getFullNameListForSelect($selectArg = 'name, \' \', surname ,\'(\',username,\')\'') {
        return $this->database->table('users')
            ->select('id, ?',Connection::literal('concat('.$selectArg.') as name'))
            ->order('surname, name')->fetchPairs('id', 'name');
    }

}