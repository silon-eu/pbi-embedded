<?php

namespace AdminModule\Controls;

use App\AdminModule\Models\Service\IconsService;
use Nette\ComponentModel\IContainer;
use Nette\Utils\Html;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;

class IconsDatagrid extends \Ublaboo\DataGrid\DataGrid
{
    public function __construct(?IContainer $parent, ?string $name, protected IconsService $iconsService)
    {
        parent::__construct($parent, $name);

        $this->setRememberState(false);

        $this->addToolbarButton(':Admin:Reporting:IconsEdit', 'Add icon')
            ->setIcon('plus')
            ->setClass('btn btn-sm btn-primary')
            ->setTitle('Add icon');

        $this->addAction('delete', 'Delete', 'deleteIcon')
            ->setIcon('trash')
            ->setClass('btn btn-sm btn-danger')
            ->setTitle('Delete icon')
            ->setConfirmation(new StringConfirmation('Are you sure you want to delete this icon?'));

        $this->setPrimaryKey('id');
        $this->setDefaultSort(['name' => 'ASC']);

        $this->addColumnNumber('id', 'Id')
            ->setSortable();

        $this->addColumnText('image', 'Image')
            ->setRenderer(function ($row) {
                return '<img src="' . IconsService::ICONS_ASSET_PATH . $row->filename . '" alt="' . $row->name . '" style="max-width: 50px; max-height: 50px;">';
            })
            ->setTemplateEscaping(false);

        $this->addColumnText('name', 'Name')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('filename', 'File');

        $this->addColumnDateTime('created_at', 'Created at')->setFormat('d.m.Y H:i:s')
            ->setSortable()
            ->setFilterDateRange();

        $this->setItemsPerPageList([10, 20, 50, 100, 500]);
        $this->setDefaultPerPage(100);
    }

    public function render(): void
    {
        $this->setDataSource($this->iconsService->getIcons());
        parent::render();
    }
}