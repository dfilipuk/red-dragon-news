<?php

namespace AppBundle\Validation;

use Symfony\Component\Form\FormInterface;

class NewCategoryValidationGroupResolver
{
    public function __invoke(FormInterface $form)
    {
        $isRootCategory = $form->get('isRootCategory')->getData();
        if ($isRootCategory) {
            return ['newRootCategory'];
        } else {
            return ['newCategory'];
        }
    }
}