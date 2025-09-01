<?php

namespace AdminModule\Controls;

use App\AdminModule\Models\Service\TagsService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Nette\ComponentModel\IContainer;
use Nette\Http\FileUpload;
use Nette\Utils\Random;
use Nette\Utils\Strings;
use Tracy\Debugger;

class TagEditForm extends \Nette\Application\UI\Control {

    public function __construct(?IContainer $container, ?string $name,
                                protected TagsService $tagsService)
    {

    }

    protected function createComponentForm(): BootstrapForm {
        $form = new BootstrapForm();
        $form->setRenderMode(RenderMode::VERTICAL_MODE);

        $form->addHidden('id', 'ID');
        $form->addText('name', 'Name')->setRequired();
        $form->addInteger('position', 'Position');
        $form->addSubmit('submit', 'Save');

        if ($this->getPresenter()->getParameter('id')) {
            $form->setDefaults($this->tagsService->getTagById($this->getPresenter()->getParameter('id'))->toArray());
        } else {
            $form->setDefaults(['position' => $this->tagsService->getNextPosition()]);
        }

        $form->onValidate[] = [$this, 'validate'];
        $form->onSuccess[] = [$this, 'save'];

        return $form;
    }

    public function validate(BootstrapForm $form, $values) {
        if ($this->tagsService->tagWithPositionExists($values->position,is_numeric($values->id) ? $values->id : null)) {
            $form['position']->addError('Tag with this position already exists.');
        }
    }

    public function save(BootstrapForm $form, $values) {

        $data = [
            'name' => $values->name,
            'position' => $values->position,
        ];

        if (!empty($values->id) && is_numeric($values->id)) {
            if ($this->tagsService->updateTag($values->id, $data)) {
                $this->getPresenter()->flashMessage('Tag updated', 'success');
            } else {
                $this->getPresenter()->flashMessage('Tag not updated', 'danger');
            }
        } else {
            if ($this->tagsService->addTag($data)) {
                $this->getPresenter()->flashMessage('Tag added', 'success');
            } else {
                $this->getPresenter()->flashMessage('Tag not added', 'danger');
            }
        }

        $this->getPresenter()->redirect('tags');
    }

    public function render() {
        $this->template->setFile(__DIR__ . '/TagEditForm.latte');
        $this->template->render();
    }

}