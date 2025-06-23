<?php

namespace App\AdminModule\Controls;

use App\AdminModule\Models\Service\ReportingService;
use Nette\ComponentModel\IContainer;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;

class PermissionsDatagrid extends \Ublaboo\DataGrid\DataGrid
{
    public function __construct(?IContainer $parent, ?string $name, protected ReportingService $reportingService)
    {
        parent::__construct($parent, $name);

        $this->setRememberState(false);

        $this->addColumnLink('tab_name', 'Tab', ':Reporting:Dashboard:default', params: ['activeTab'=>'tab_id'])
            ->setSortable()
            ->setOpenInNewTab()
            ->setFilterSelect($reportingService->getTabs()->order('name')->fetchPairs('name', 'name'))
            ->setPrompt('-- choose --');

        $this->addColumnLink('tile_name', 'Tile', ':Reporting:Report:detail', params: ['id' => 'tile_id'])
            ->setSortable()
            ->setOpenInNewTab()
            ->setFilterSelect($reportingService->getTiles()->order('name')->fetchPairs('name', 'name'))
            ->setPrompt('-- choose --');

        $this->addColumnLink('page_name', 'Page', ':Reporting:Report:detail', params: ['id' => 'tile_id', 'activePageId' => 'page_id'])
            ->setSortable()
            ->setOpenInNewTab()
            ->setFilterText();

        $this->addColumnLink('group_name', 'Group', ':Admin:Groups:edit', params: ['id' => 'group_id'])
            ->setSortable()
            ->setOpenInNewTab()
            ->setFilterText();

        $this->addColumnLink('group_user_name', 'User from group', ':Admin:Users:edit', params: ['id' => 'group_user_id'])
            ->setSortable()
            ->setOpenInNewTab()
            ->setFilterText(['group_user_name', 'user_name'])->setPlaceholder('Search user from group or directly set on page');

        $this->addColumnLink('user_name', 'User', ':Admin:Users:edit', params: ['id' => 'user_id'])
            ->setSortable()
            ->setOpenInNewTab();

        $this->setItemsPerPageList([10, 20, 50, 100, 500]);
        $this->setDefaultPerPage(100);
        $this->addExportCsv('Csv export', 'reporting-permissions.csv', filtered: true)
            ->setTitle('Csv export');
    }

    public function render(): void
    {
        $this->setPrimaryKey('page_id');
        $this->setDataSource($this->reportingService->getPermissions()->fetchAll());
        parent::render();
    }

    public function renderGroup(int $groupId): void
    {
        $this->setPrimaryKey('page_id');
        $this->setDataSource($this->reportingService->getGroupPermissions($groupId)->fetchAll());
        $this->removeColumn('user_name');
        $this->removeColumn('group_name');
        $this->removeColumn('group_user_name');
        parent::render();
    }

    public function renderUser(int $userId): void
    {
        $this->setDataSource($this->reportingService->getUserPermissions($userId)->fetchAll());
        $this->removeColumn('group_user_name');
        parent::render();
    }
}