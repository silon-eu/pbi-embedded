<?php

namespace App\ReportingModule\Models\Service;

use Nette\Database\Explorer;
use Nette\Database\Table\Selection;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

class ReportService extends BaseService
{
    public function __construct(
        protected Explorer $database,
        protected \Nette\Caching\Cache $cache
    ) {

    }

    public function getPages(): Selection
    {
        return $this->database->table('rep_pages')
            ->order('position');
    }

    public function getPagesForTile(int $tileId): Selection
    {
        return $this->getPages()->where('rep_tiles_id = ?', $tileId);
    }

    public function getNavigationForTile(int $tileId, int $userId, bool $isAdmin = false): array
    {
        $pages = $this->getPagesForTile($tileId);

        $navigation = [];
        foreach ($pages as $page) {
            $permissions = $this->getPermissionsForPage($page->id);
            $canView = false;
            if (!$isAdmin && $userId !== null) {
                // Filter permissions for the current user
                $canView = $permissions->where('users_id = ? OR groups_id IN (SELECT groups_id FROM users WHERE id = ?)', $userId, $userId)->count() > 0;
            } elseif ($isAdmin) {
                // Admins can view all pages
                $canView = true;
            }

            if ($canView) {
                $navigation[] = [
                    'id' => $page->id,
                    'name' => $page->name,
                    'page' => $page->page,
                    'filters' => $page->filters,
                    'slicers' => $page->slicers,
                    'hasPermissions' => $permissions->count() > 0,
                ];
            }
        }

        return $navigation;
    }

    public function addPage(object $values): int
    {
        // get max position from pages
        $maxPosition = $this->getPagesForTile($values->rep_tiles_id)->max('position');

        $tile = $this->getPages()->insert([
            'name' => $values->name,
            'position' => intval($maxPosition) + 1,
            'description' => $values->description,
            'page' => $values->page,
            'rep_tiles_id' => $values->rep_tiles_id,
        ]);
        return $tile->id;
    }

    public function editPage(object $values): void
    {
        $this->getPages()->get($values->id)
            ->update([
                'name' => $values->name,
                'description' => $values->description,
                'page' => $values->page,
                'rep_tiles_id' => $values->rep_tiles_id,
                'filters' => $values->filters,
                'slicers' => $values->slicers,
            ]);

        if (isset($values->group_permissions)) {
            $this->updateGroupPagePermissions($values->id,$values->group_permissions);
        } else {
            // If group permissions are not set, clear existing group permissions
            $this->getGroupPermissionsForPage($values->id)->delete();
        }

        if (isset($values->user_permissions)) {
            $this->updateUserPagePermissions($values->id,$values->user_permissions);
        } else {
            // If user permissions are not set, clear existing user permissions
            $this->getUserPermissionsForPage($values->id)->delete();
        }
    }

    public function deletePage(int $id): void
    {
        $this->getPages()->get($id)->delete();
    }

    public function changePagePosition(int $id, string $position): void
    {
        $page = $this->getPages()->get($id);

        if ($position === 'increase') {
            $nextTile = $this->getPagesForTile($page->rep_tiles_id)
                ->where('position = ?', $page->position + 1)
                ->fetch();

            if ($nextTile) {
                $nextTile->update(['position' => $page->position]);
                $page->update(['position' => $page->position + 1]);
            }
        } elseif ($position === 'decrease') {
            $prevTile = $this->getPagesForTile($page->rep_tiles_id)
                ->where('position = ?', $page->position - 1)
                ->fetch();

            if ($prevTile) {
                $prevTile->update(['position' => $page->position]);
                $page->update(['position' => $page->position - 1]);
            }
        }
    }

    public function updatePageFilters(int $id, string $filters): void
    {
        $page = $this->getPages()->get($id);
        if ($page) {
            $page->update(['filters' => $filters]);
        }
    }

    public function getPermissions(): Selection
    {
        return $this->database->table('rep_pages_permissions');
    }

    public function getPermissionsForPage(int $pageId): Selection
    {
        return $this->getPermissions()->where('rep_pages_id = ?', $pageId);
    }

    public function getGroupPermissionsForPage(int $pageId): Selection
    {
        return $this->getPermissionsForPage($pageId)->where('groups_id IS NOT NULL');
    }

    public function getUserPermissionsForPage(int $pageId): Selection
    {
        return $this->getPermissionsForPage($pageId)->where('users_id IS NOT NULL');
    }

    public function updatePagePermissions(int $pageId, string $column, ArrayHash $permissions): void
    {
        // Delete existing permissions for the page
        if (!in_array($column, ['groups_id', 'users_id'])) {
            throw new \InvalidArgumentException("Invalid column name: $column");
        } elseif ($column === 'groups_id') {
            $this->getGroupPermissionsForPage($pageId)->delete();
        } elseif ($column === 'users_id') {
            $this->getUserPermissionsForPage($pageId)->delete();
        }

        // Insert new permissions
        foreach ($permissions as $permission) {
            $this->getPermissions()->insert([
                'rep_pages_id' => $pageId,
                $column => $permission,
            ]);
        }
    }

    public function updateGroupPagePermissions(int $pageId, ArrayHash $groups): void
    {
        $this->updatePagePermissions($pageId, 'groups_id', $groups);
    }

    public function updateUserPagePermissions(int $pageId, ArrayHash $users): void
    {
        $this->updatePagePermissions($pageId, 'users_id', $users);
    }

}