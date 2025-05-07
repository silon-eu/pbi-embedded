<?php

namespace App\ReportingModule\Models\Service;

use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

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
}