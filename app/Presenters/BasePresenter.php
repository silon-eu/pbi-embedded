<?php

//declare(strict_types=1);

namespace App\Presenters;

use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\Translation\Translator;
use Nette;
use App\TokenStore\TokenCache;
use GraphHelper;
use Ublaboo\DataGrid\DataGrid;

abstract class BasePresenter extends Nette\Application\UI\Presenter {

    /** @var \Contributte\MenuControl\UI\MenuComponentFactory @inject  */
    public $menuFactory;

    /** @var \App\Model\Authenticator @inject */
    public $authenticator;

    /** @var Translator @inject */
    public $translator;

    /** @var \Contributte\Translation\LocalesResolvers\Session @inject */
    public $translatorSessionResolver;

    protected function createComponent($name): \Nette\ComponentModel\IComponent {
        switch($name) {
            case 'adminMenu':
                return $this->menuFactory->create('admin');
            case 'frontMenu':
                return $this->menuFactory->create('front');
            case 'loginForm':
                $loginForm = new \Nette\Application\UI\Form();
                $loginForm->setTranslator($this->translator);
                $loginForm->addText('username','Username');
                $loginForm->addPassword('password','Password');
                $loginForm->addSubmit('send','Sign in');
                $loginForm->onSuccess[] = [$this, 'processLogin'];
                return $loginForm;
            default:
                return parent::createComponent($name);
        }
    }

    public function startup(): void
    {
        parent::startup();

        // load roles for CLI user
        if (PHP_SAPI === 'cli' && !$this->getUser()->isLoggedIn()) {
            $this->getUser()->login('',null);
        } else if (!$this->user->isLoggedIn() && $this->getPresenter()->getName() !== 'Auth') {
            $this->redirect(':Auth:login');
        }

        DataGrid::$iconPrefix = 'bi bi-';
        BootstrapForm::switchBootstrapVersion(5);
    }

    public function handleChangeLocale(string $locale): void
    {
        $this->translatorSessionResolver->setLocale($locale);
        $this->redirect('this');
    }

    protected function beforeRender(): void {
        $this->template->setTranslator($this->translator);
        $this->template->translator = $this->translator;
        $this->template->userIdentityData = $this->getUser()->getIdentity()?->getData();
    }

    public function processLogin(\Nette\Application\UI\Form $form, $values): void {
        try {
            $identity = $this->authenticator->authenticate($values->username,$values->password);
            $this->getUser()->login($identity);
            $this->redirect(':Reporting:Dashboard:default');
        } catch (\Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

    protected function allowOnlyRoles(array $roles): void
    {
        if (count(array_intersect($this->getUser()->getRoles(),$roles)) === 0) {
            throw new \Nette\Application\ForbiddenRequestException('Insufficient privileges');
        }
    }

    public function getTranslator(): Translator {
        return $this->translator;
    }

}
