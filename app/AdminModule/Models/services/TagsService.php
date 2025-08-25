<?php

namespace App\AdminModule\Models\Service;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Tracy\Debugger;

class TagsService extends BaseService
{
    public function __construct(
        protected Explorer $database
    ) {

    }

    public function getTags(): Selection
    {
        return $this->database->table('rep_tags');
    }

    public function getTagById(int $id): ?ActiveRow
    {
        return $this->database->table('rep_tags')->get($id);
    }

    public function addTag(array|\Nette\Utils\ArrayHash $data): ?ActiveRow
    {
        return $this->database->table('rep_tags')->insert($data);
    }

    public function updateTag(int $id, array|\Nette\Utils\ArrayHash $data): bool
    {
        $tag = $this->database->table('rep_tags')->get($id);
        if ($tag) {
            $tag->update($data);
            return true;
        }
        return false;
    }

    public function getTagsForTabTiles(array $tabTiles): array
    {
        $tagsForTabs = [];
        foreach ($tabTiles as $tabId => $tiles) {
            foreach ($tiles as $tile) {
                $tags = $tile->related('rep_tiles_tags')->fetchAll();
                foreach ($tags as $tag) {
                    $tagsForTabs[$tabId][$tag->rep_tags_id] = $tag->rep_tags->name;
                }
            }
        }
        return $tagsForTabs;
    }

}