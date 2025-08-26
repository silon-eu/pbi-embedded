<?php

namespace App\Model;

use Nette;
use Nette\Security\SimpleIdentity;

class Authenticator implements Nette\Security\Authenticator, \Nette\Security\IdentityHandler
{
    public function __construct(
        private Nette\Database\Explorer $database,
        private \LdapRecord\Connection $ldapConnection,
        private Nette\Caching\Cache $cache,
        private array $params
    ) {
    }

    public function authenticate(string $username, ?string $password): SimpleIdentity
    {
        // if CLI, load roles form DB
        if (PHP_SAPI === 'cli') {
            if (isset($this->params['cli_user'])) {
                $user = $this->database->table('users')->where('username',$this->params['cli_user'])->fetch();
                if ($user) {
                    return new SimpleIdentity(
                        $user->id,
                        $this->getRoles($user->id),
                        [
                            'username' => $user->username,
                            'name' => $user->name,
                            'surname' => $user->surname
                        ],
                    );
                } else {
                    throw new Nette\Security\AuthenticationException('CLI authentication failed. User not found.');
                }
            } else {
                throw new Nette\Security\AuthenticationException('CLI authentication failed. User is not defined in parameters.');
            }
        }

        // if not CLI, try LDAP
        $user = $this->ldapConnection->query()
            ->where('samaccountname', '=', $username)
            ->first();

        if (!$user) {
            throw new Nette\Security\AuthenticationException('User not found in Active Directory.');
        }

        if (!$this->ldapConnection->auth()->attempt($user['distinguishedname'][0], $password)) {
            throw new Nette\Security\AuthenticationException('Invalid password.');
        }

        $row = $this->database->table('users')
            ->where('username', $username)
            ->fetch();
        if (!$row) {
            throw new Nette\Security\AuthenticationException('Account is not created.');
        } else {
            if ($row->active != 1) {
                throw new Nette\Security\AuthenticationException('Account is not active.');
            }
            return new SimpleIdentity(
                $row->id,
                $this->getRoles($row->id),
                [
                    'username' => $row->username,
                    'name' => $user['givenname'][0],
                    'surname' => $user['sn'][0]
                ],
            );
        }
    }

    public function getRoles(int $userId): array
    {
        return $this->database->query('
            select
                r.name
            from
                users u
                join groups_roles gr on gr.groups_id = u.groups_id 
                join roles r on gr.roles_id = r.id 
            where
                u.id = ?',$userId,'
                
            union
            
            select
                r.name 
            from 
                users_roles ur
                join roles r on ur.roles_id = r.id 
            where 
                users_id = ?
        ',$userId)->fetchPairs(null,'name');
    }

    public function sleepIdentity(\Nette\Security\IIdentity $identity): \Nette\Security\IIdentity
    {
        // zde lze pozměnit identitu před zápisem do úložiště po přihlášení
        return $identity;
    }

    public function wakeupIdentity(\Nette\Security\IIdentity $identity): ?\Nette\Security\IIdentity
    {

        $userId = $identity->getId();

        $roles = $this->cache->load('sys_authenticator_roles_'.$userId, function(&$dependencies) use ($userId) {
            $dependencies[\Nette\Caching\Cache::Expire] = '24 hour';
            $dependencies[\Nette\Caching\Cache::Tags] = ['sys_authenticator_roles'];
            return $this->getRoles($userId);
        });
        $identity->setRoles($roles);
        return $identity;
    }
}