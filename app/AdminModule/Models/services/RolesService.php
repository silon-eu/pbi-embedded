<?php

namespace App\AdminModule\Models\Service;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Utils\ArrayHash;

class RolesService extends BaseService
{
    protected $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function getDatasource(): Selection
    {
        return $this->database->table('roles');
    }

    public function getRole(int $id): ?ActiveRow
    {
        return $this->database->table('roles')->get($id);
    }

    public function getGroups(int $id): Selection
    {
        return $this->database->table('groups_roles')->where('roles_id', $id);
    }

    public function createRole(array|ArrayHash $values): ?ActiveRow
    {
        $data = clone $values;
        unset($data['groups']);
        $row = $this->database->table('roles')->insert($data);
        $this->updateGroups($row->id, $values['groups']);
        return $row;
    }

    public function updateRole(int $id, array|ArrayHash $values): bool
    {
        $data = clone $values;
        unset($data['groups']);
        $update = $this->database->table('roles')->get($id)?->update($data);
        $roles = $this->updateGroups($id, $values['groups']);
        return ($update || $roles);
    }

    public function deleteRole(int $id): ?int
    {
        $this->database->table('groups_roles')->where('roles_id', $id)->delete();
        return $this->database->table('roles')->get($id)?->delete();
    }

    protected function updateGroups(int $id, array $groups): bool
    {
        $this->database->table('groups_roles')->where('roles_id', $id)->delete();
        foreach ($groups as $groupId) {
            $this->database->table('groups_roles')->insert(['roles_id' => $id, 'groups_id' => $groupId]);
        }
        return true;
    }

}