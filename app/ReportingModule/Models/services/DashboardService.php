<?php

namespace App\ReportingModule\Models\Service;

use Nette\Database\Explorer;
use Nette\Database\ResultSet;
use Nette\Database\Table\Selection;
use Tracy\Debugger;

class DashboardService extends BaseService
{
    public function __construct(
        protected Explorer $database,
        protected \Nette\Caching\Cache $cache
    ) {

    }

    public function getTabs(): Selection
    {
        return $this->database->table('rep_tabs')
            ->order('position');
    }

    public function getUserAccess(int $userId): Selection
    {
        return $this->database->table('rep_pages_permissions')
            ->where('rep_pages_permissions.users_id = ? OR rep_pages_permissions.groups_id IN (SELECT groups_id FROM users WHERE id = ?)', $userId, $userId);
    }

    public function getUserTabs(int $userId): array
    {
        $access = $this->getUserAccess($userId);
        $tabs = [];
        foreach ($access as $item) {
            $tab = $item->rep_pages->rep_tiles->rep_tabs;
            $tabs[$tab->position] = $tab;
        }
        Debugger::barDump($tabs,'tabs');
        return $tabs;
    }

    public function getUserTilesForAllTabs(int $userId): array
    {
        $access = $this->getUserAccess($userId);
        $tiles = [];
        foreach ($access as $item) {
            $tile = $item->rep_pages->rep_tiles;
            $tiles[$tile->rep_tabs_id][$tile->position] = $tile;
        }
        // Sort tiles by position within each tab
        foreach ($tiles as $tabId => $tabTiles) {
            ksort($tiles[$tabId]);
        }
        Debugger::barDump($tiles,'tiles');
        return $tiles;
    }

    public function addTab(string $name): int
    {
        // get max position from tabs
        $maxPosition = $this->database->table('rep_tabs')
            ->select('MAX(position) AS max')
            ->fetch()->max;

        $tab = $this->database->table('rep_tabs')->insert([
            'name' => $name,
            'position' => intval($maxPosition) + 1,
        ]);
        return $tab->id;
    }

    public function editTab(int $id, string $name): void
    {
        $this->database->table('rep_tabs')
            ->where('id = ?', $id)
            ->update([
                'name' => $name,
            ]);
    }

    public function deleteTab(int $id): void
    {
        $this->database->table('rep_tabs')
            ->where('id = ?', $id)
            ->delete();
    }

    public function changeTabPosition(int $id, string $position): void
    {
        $tab = $this->database->table('rep_tabs')
            ->where('id = ?', $id)->fetch();

        if ($position === 'increase') {
            $nextTab = $this->database->table('rep_tabs')
                ->where('position = ?', $tab->position + 1)
                ->fetch();

            if ($nextTab) {
                $this->database->table('rep_tabs')
                    ->where('id = ?', $nextTab->id)
                    ->update(['position' => $tab->position]);

                $this->database->table('rep_tabs')
                    ->where('id = ?', $tab->id)
                    ->update(['position' => $tab->position + 1]);
            }
        } elseif ($position === 'decrease') {
            $prevTab = $this->database->table('rep_tabs')
                ->where('position = ?', $tab->position - 1)
                ->fetch();

            if ($prevTab) {
                $this->database->table('rep_tabs')
                    ->where('id = ?', $prevTab->id)
                    ->update(['position' => $tab->position]);

                $this->database->table('rep_tabs')
                    ->where('id = ?', $tab->id)
                    ->update(['position' => $tab->position - 1]);
            }
        }
    }

    public function getTiles(): Selection
    {
        return $this->database->table('rep_tiles')
            ->order('position');
    }

    public function getTilesForTab(int $tabId): Selection
    {
        return $this->getTiles()->where('rep_tabs_id = ?', $tabId);
    }

    public function getTilesForAllTabs(): array
    {
        $tabs = $this->getTabs();
        $tiles = [];
        foreach ($tabs as $tab) {
            $tiles[$tab->id] = $this->getTilesForTab($tab->id);
        }
        return $tiles;
    }

    public function addTile(object $values): int
    {
        // get max position from tiles
        $maxPosition = $this->getTilesForTab($values->rep_tabs_id)
            ->select('MAX(position) AS max')
            ->fetch()->max;

        $tile = $this->database->table('rep_tiles')->insert([
            'name' => $values->name,
            'position' => intval($maxPosition) + 1,
            'description' => $values->description,
            'icon' => $values->icon,
            'workspace' => $values->workspace,
            'report' => $values->report,
            'rep_tabs_id' => $values->rep_tabs_id,
        ]);
        return $tile->id;
    }

    public function editTile(object $values): void
    {
        $this->database->table('rep_tiles')
            ->where('id = ?', $values->id)
            ->update([
                'name' => $values->name,
                'description' => $values->description,
                'icon' => $values->icon,
                'workspace' => $values->workspace,
                'report' => $values->report,
                'rep_tabs_id' => $values->rep_tabs_id,
            ]);
    }

    public function deleteTile(int $id): void
    {
        $this->getTiles()->get($id)->delete();
    }

    public function changeTilePosition(int $id, string $position): void
    {
        $tile = $this->getTiles()->get($id);

        if ($position === 'increase') {
            $nextTile = $this->getTilesForTab($tile->rep_tabs_id)
                ->where('position = ?', $tile->position + 1)
                ->fetch();

            if ($nextTile) {
                $nextTile->update(['position' => $tile->position]);
                $tile->update(['position' => $tile->position + 1]);
            }
        } elseif ($position === 'decrease') {
            $prevTile = $this->getTilesForTab($tile->rep_tabs_id)
                ->where('position = ?', $tile->position - 1)
                ->fetch();

            if ($prevTile) {
                $prevTile->update(['position' => $tile->position]);
                $tile->update(['position' => $tile->position - 1]);
            }
        }
    }

    public function updateReportFilters(int $id, string $filters): void
    {
        $tile = $this->getTiles()->get($id);
        if ($tile) {
            $tile->update(['filters' => $filters]);
        }
    }
}