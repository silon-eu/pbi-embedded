<?php

namespace AdminModule\Controls;

use App\AdminModule\Models\Service\TagsService;
use Nette\ComponentModel\IContainer;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;

class TagsDatagrid extends \Ublaboo\DataGrid\DataGrid
{
    public function __construct(?IContainer $parent, ?string $name, protected TagsService $tagsService)
    {
        parent::__construct($parent, $name);

        $this->setRememberState(false);

        $this->addToolbarButton(':Admin:Reporting:tagsEdit', 'Add tag')
            ->setIcon('plus')
            ->setClass('btn btn-sm btn-primary')
            ->setTitle('Add icon');

        $this->addAction('edit', 'Edit', 'tagsEdit', ['id' => 'id'])
            ->setIcon('pencil')
            ->setClass('btn btn-sm btn-secondary')
            ->setTitle('Edit tag');

        $this->addAction('delete', 'Delete', 'deleteTag')
            ->setIcon('trash')
            ->setClass('btn btn-sm btn-danger')
            ->setTitle('Delete icon')
            ->setConfirmation(new StringConfirmation('Are you sure you want to delete this tag?'));

        $this->setPrimaryKey('id');
        $this->setDefaultSort(['position' => 'ASC']);

        $this->addColumnNumber('id', 'Id')
            ->setSortable();

        $this->addColumnText('name', 'Name')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('position', 'Position')
            ->setSortable();

        $this->setItemsPerPageList([10, 20, 50, 100, 500]);
        $this->setDefaultPerPage(100);
    }

    public function render(): void
    {
        $this->setDataSource($this->tagsService->getTags());
        parent::render();
    }
}