<?php

namespace App\ReportingModule\Models\Service;

use Nette\Database\Explorer;
use Nette\Database\Table\Selection;
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

    public function getNavigationForTile(int $tileId): array
    {
        $pages = $this->getPagesForTile($tileId);

        $navigation = [];
        foreach ($pages as $page) {
            $navigation[$page->page] = [
                'id' => $page->id,
                'name' => $page->name,
                'page' => $page->page,
            ];
        }

        return $navigation;
    }

    public function addPage(object $values): int
    {
        // get max position from pages
        $maxPosition = $this->getPagesForTile($values->rep_tiles_id)
            ->select('MAX(position) AS max')
            ->fetch()->max;

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
            ]);
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
}