<?php

namespace App\AdminModule\Models\Service;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

class GroupsService extends BaseService
{
    protected $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function getDatasource(): Selection
    {
        return $this->database->table('groups');
    }

    public function getKeyPairs(): array {
        return $this->database->table('groups')->fetchPairs('id', 'name');
    }

    public function getGroup(int $id): ?ActiveRow
    {
        return $this->database->table('groups')->get($id);
    }

    public function getRoles(int $id): Selection
    {
        return $this->database->table('groups_roles')->where('groups_id', $id);
    }

    public function createGroup(array|ArrayHash $values): ?ActiveRow
    {
        $data = clone $values;
        unset($data['roles']);
        $row = $this->database->table('groups')->insert($data);
        $this->updateRoles($row->id, $values['roles']);
        return $row;
    }

    public function updateGroup(int $id, array|ArrayHash $values): bool
    {
        $data = clone $values;
        unset($data['roles']);
        $update = $this->database->table('groups')->get($id)?->update($data);
        $roles = $this->updateRoles($id, $values['roles']);
        return ($update || $roles);
    }

    public function deleteGroup(int $id): ?int
    {
        $this->database->table('groups_roles')->where('groups_id', $id)->delete();
        return $this->database->table('groups')->get($id)?->delete();
    }

    protected function updateRoles(int $id, array $roles): bool
    {
        $this->database->table('groups_roles')->where('groups_id', $id)->delete();
        foreach ($roles as $roleId) {
            $this->database->table('groups_roles')->insert(['groups_id' => $id, 'roles_id' => $roleId]);
        }
        return true;
    }

}