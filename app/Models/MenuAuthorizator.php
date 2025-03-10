<?php

namespace App\Model;

final class MenuAuthorizator implements \Contributte\MenuControl\Security\IAuthorizator
{
    protected \Nette\Security\User $user;

    public function __construct(\Nette\Security\User $user)
    {
        $this->user = $user;
    }

    public function isMenuItemAllowed(\Contributte\MenuControl\IMenuItem $item): bool
    {
        $itemRoles = $item->getData()['roles'] ?? [];
        if ($this->user->isInRole('admin')) {
            return true;
        } else if (!is_null($itemRoles)) {
            return count(array_intersect($this->user->getRoles(),$itemRoles)) > 0;
        } else {
            return true;
        }
    }
}