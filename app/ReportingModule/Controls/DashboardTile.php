<?php

namespace App\ReportingModule\Controls;

use App\AdminModule\Models\Service\GroupsService;
use App\AdminModule\Models\Service\RolesService;
use App\AdminModule\Models\Service\UsersService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Nette\Caching\Cache;
use Nette\ComponentModel\IContainer;

class DashboardTile extends \Nette\Application\UI\Control {

    public function render(string $link, string $icon, string $name, string $description = '', string $class = ''): void{
        $this->template->setFile(__DIR__ . '/DashboardTile.latte');
        $this->template->link = $link;
        $this->template->icon = $icon;
        $this->template->name = $name;
        $this->template->description = $description;
        $this->template->class = $class;
        $this->template->render();
    }

}