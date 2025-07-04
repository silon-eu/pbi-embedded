<?php

namespace AdminModule\Controls;

use App\AdminModule\Models\Service\ReportingService;
use App\ReportingModule\Models\Service\AccessLogService;
use Nette\ComponentModel\IContainer;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;

class AccessLogDatagrid extends \Ublaboo\DataGrid\DataGrid
{
    public function __construct(?IContainer $parent, ?string $name, protected ReportingService $reportingService, protected AccessLogService $accessLogService)
    {
        parent::__construct($parent, $name);

        $this->setRememberState(false);

        $this->setPrimaryKey('id');
        $this->setDefaultSort(['id' => 'DESC']);

        $this->addColumnNumber('id', 'Id')
            ->setSortable();

        $this->addColumnLink('tab_name', 'Tab', ':Reporting:Dashboard:default', 'rep_tabs.name', params: ['activeTab'=>'rep_tabs_id'])
            ->setSortable()
            ->setOpenInNewTab()
            ->setFilterSelect($reportingService->getTabs()->order('name')->fetchPairs('id', 'name'), 'rep_tabs_id')
            ->setPrompt('-- choose --');

        $this->addColumnLink('tile_name', 'Tile', ':Reporting:Report:detail', 'rep_tiles.name', params: ['id' => 'rep_tiles_id'])
            ->setSortable()
            ->setOpenInNewTab()
            ->setFilterSelect($reportingService->getTiles()->order('name')->fetchPairs('id', 'name'), 'rep_tiles_id')
            ->setPrompt('-- choose --');

        $this->addColumnLink('page_name', 'Page', ':Reporting:Report:detail', 'rep_pages.name', params: ['id' => 'rep_tiles_id', 'activePageId' => 'rep_pages_id'])
            ->setSortable()
            ->setOpenInNewTab()
            ->setFilterText();

        $this->addColumnLink('user_name', 'User', ':Admin:Users:edit', 'users.username', params: ['id' => 'users_id'])
            ->setSortable()
            ->setOpenInNewTab()
            ->setFilterText();

        $this->addColumnDateTime('created_at', 'Accessed at')->setFormat('d.m.Y H:i:s')
            ->setFilterDateRange();

        $this->setItemsPerPageList([10, 20, 50, 100, 500]);
        $this->setDefaultPerPage(100);
        $this->addExportCsv('Csv export', 'reporting-access-log.csv', filtered: true)
            ->setTitle('Csv export');
    }

    public function render(): void
    {
        $this->setDataSource($this->accessLogService->getAccessLog());
        parent::render();
    }
}