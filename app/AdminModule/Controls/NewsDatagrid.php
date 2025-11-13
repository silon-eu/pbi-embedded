<?php

namespace AdminModule\Controls;

use App\AdminModule\Models\Service\IconsService;
use App\AdminModule\Models\Service\NewsService;
use Nette\ComponentModel\IContainer;
use Nette\Utils\Html;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;

class NewsDatagrid extends \Ublaboo\DataGrid\DataGrid
{
    public function __construct(?IContainer $parent, ?string $name, protected NewsService $newsService)
    {
        parent::__construct($parent, $name);

        $this->setRememberState(false);

        $this->addToolbarButton(':Admin:Reporting:newsEdit', 'Add news')
            ->setIcon('plus')
            ->setClass('btn btn-sm btn-primary')
            ->setTitle('Add news');

        $this->addAction('edit', 'Edit', ':Admin:Reporting:newsEdit')
            ->setIcon('pencil')
            ->setClass('btn btn-sm btn-secondary')
            ->setTitle('Edit news');

        $this->addAction('delete', 'Delete', 'deleteNews')
            ->setIcon('trash')
            ->setClass('btn btn-sm btn-danger')
            ->setTitle('Delete icon')
            ->setConfirmation(new StringConfirmation('Are you sure you want to delete this news?'));

        $this->setPrimaryKey('id');
        $this->setDefaultSort(['created_at' => 'DESC']);

        $this->addColumnNumber('id', 'Id')
            ->setSortable();

        $this->addColumnText('name', 'Name')
            ->setSortable()
            ->setFilterText();

        $this->addColumnDateTime('created_at', 'Created at')->setFormat('d.m.Y H:i')
            ->setSortable();

        $this->addColumnDateTime('valid_from', 'Valid from')->setFormat('d.m.Y H:i')
            ->setSortable();

        $this->addColumnDateTime('valid_to', 'Valid to')->setFormat('d.m.Y H:i')
            ->setSortable();

        $this->setItemsPerPageList([10, 20, 50, 100, 500]);
        $this->setDefaultPerPage(100);
    }

    public function render(): void
    {
        $this->setDataSource($this->newsService->getNews());
        parent::render();
    }
}