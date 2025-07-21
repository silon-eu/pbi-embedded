<?php

namespace AdminModule\Controls;

use App\AdminModule\Models\Service\IconsService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Nette\ComponentModel\IContainer;
use Nette\Http\FileUpload;
use Nette\Utils\Random;
use Nette\Utils\Strings;
use Tracy\Debugger;

class IconEditForm extends \Nette\Application\UI\Control {

    public function __construct(?IContainer $container, ?string $name,
                                protected IconsService $iconsService)
    {

    }

    protected function createComponentForm(): BootstrapForm {
        $form = new BootstrapForm();
        $form->setRenderMode(RenderMode::VERTICAL_MODE);

        $form->addHidden('id', 'ID');
        $form->addText('name', 'Name')->setRequired();
        $form->addUpload('filename', 'File')
            ->setRequired()
            ->addRule($form::Image, 'File must be an image')
            ->setOption('description', 'Download PNG icons from <a href="https://www.flaticon.com/search?color=black&shape=outline" target="_blank">Flaticon</a>');

        $form->addSubmit('submit', 'Save');

        /*if ($this->getPresenter()->getParameter('id')) {
            $form->setDefaults($this->iconsService->getIconById($this->getPresenter()->getParameter('id'))->toArray());
        }*/

        $form->onSuccess[] = [$this, 'save'];

        return $form;
    }

    public function save($form, $values) {

        /** @var FileUpload $file */
        $file = $values->filename;
        if ($file->isOk()) {
            $data = [
                'name' => $values->name,
                'filename' => Strings::webalize($file->getSanitizedName()) . '-' . Random::generate(). '.' .$file->getSuggestedExtension(),
            ];
            $file->move(IconsService::ICONS_PATH . $data['filename']);

            if ($this->iconsService->addIcon($data)) {
                $this->getPresenter()->flashMessage('Icon added', 'success');
                $this->getPresenter()->redirect('icons');
            } else {
                $this->getPresenter()->flashMessage('Icon not added', 'danger');
            }
        } else {
            $this->getPresenter()->flashMessage('File upload error: ' . $file->getError(), 'danger');
        }

        $this->getPresenter()->redirect('this');
    }

    public function render() {
        $this->template->setFile(__DIR__ . '/IconEditForm.latte');
        $this->template->render();
    }

}