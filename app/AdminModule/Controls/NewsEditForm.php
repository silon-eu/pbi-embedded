<?php

namespace AdminModule\Controls;

use App\AdminModule\Models\Service\IconsService;
use App\AdminModule\Models\Service\NewsService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Nette\ComponentModel\IContainer;
use Nette\Http\FileUpload;
use Nette\Utils\Random;
use Nette\Utils\Strings;
use Tracy\Debugger;

class NewsEditForm extends \Nette\Application\UI\Control {

    public function __construct(?IContainer $container, ?string $name,
                                protected NewsService $newsService)
    {

    }

    protected function createComponentForm(): BootstrapForm {
        $form = new BootstrapForm();
        $form->setRenderMode(RenderMode::VERTICAL_MODE);

        $form->addHidden('id', 'ID');
        $form->addText('name', 'Name')->setRequired()
            ->setMaxLength(100);
        $form->addTextArea('text', 'Text')->setRequired()->getControlPrototype()->appendAttribute('class','tinymce');

        $form->addDateTime('created_at', 'Created at')->setRequired()->setDefaultValue(new \DateTime());

        $form->addDateTime('valid_from', 'Valid from')->setRequired()->setDefaultValue(new \DateTime());
        $form->addDateTime('valid_to', 'Valid to');


        $form->addSubmit('submit', 'Save');

        if ($this->getPresenter()->getParameter('id')) {
            $form->setDefaults($this->newsService->getNewsById($this->getPresenter()->getParameter('id'))->toArray());
        }

        $form->onSuccess[] = [$this, 'save'];

        return $form;
    }

    public function save($form, array $values) {
        unset($values['save']);
        if (is_numeric($values['id']) && intval($values['id']) > 0) {
            if ($this->newsService->updateNews(intval($values['id']), $values)) {
                $this->getPresenter()->flashMessage('News updated', 'success');
                $this->getPresenter()->redirect('news');
            } else {
                $this->getPresenter()->flashMessage('News not updated', 'danger');
            }
        } else {
            if ($this->newsService->createNews($values)) {
                $this->getPresenter()->flashMessage('News added', 'success');
                $this->getPresenter()->redirect('news');
            } else {
                $this->getPresenter()->flashMessage('News not added', 'danger');
            }
        }

        $this->getPresenter()->redirect('this');
    }

    public function render() {
        $this->template->setFile(__DIR__ . '/NewsEditForm.latte');
        $this->template->render();
    }

}