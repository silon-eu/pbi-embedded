<?php

namespace App\AdminModule\Models\Service;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class NewsService extends BaseService
{
    public function __construct(
        protected Explorer $database
    ) {

    }

    public function getNews(): Selection
    {
        return $this->database->table('news');
    }

    public function getNewsfeed(int $limit = 5): Selection
    {
        return $this->getNews()
            ->where('valid_from <= ?', new \DateTime())->where('valid_to IS NULL OR valid_to >= ?', new \DateTime())
            ->order('created_at DESC')
            ->limit($limit);
    }

    public function getNewsById(int $id): ActiveRow
    {
        return $this->getNews()
            ->get($id);
    }

    public function createNews(array $data): ActiveRow
    {
        return $this->getNews()
            ->insert($data);
    }

    public function updateNews(int $id, array $data): int
    {
        return $this->getNews()
            ->get($id)
            ->update($data);
    }

    public function deleteNews(int $id): int
    {
        return $this->getNews()
            ->get($id)
            ->delete();
    }


}