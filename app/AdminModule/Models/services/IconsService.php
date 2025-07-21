<?php

namespace App\AdminModule\Models\Service;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class IconsService extends BaseService
{
    public const ICONS_PATH = __DIR__.'/../../../../assets/images/icons/';
    public const ICONS_ASSET_PATH = '/bi/assets/images/icons/';

    public function __construct(
        protected Explorer $database
    ) {

    }

    public function getIcons(): Selection
    {
        return $this->database->table('rep_icons');
    }

    public function getIconById(int $id): ?ActiveRow
    {
        return $this->database->table('rep_icons')->get($id);
    }

    public function addIcon(array|\Nette\Utils\ArrayHash $data): ?ActiveRow
    {
        return $this->database->table('rep_icons')->insert($data);
    }

    public function updateIcon(int $id, array|\Nette\Utils\ArrayHash $data): bool
    {
        $icon = $this->database->table('rep_icons')->get($id);
        if ($icon) {
            $icon->update($data);
            return true;
        }
        return false;
    }

}