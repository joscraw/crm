<?php

namespace App\Utils;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

trait FormHelper
{

    /**
     * @param Request $request
     * @param FormInterface $form
     */
    private function submit(Request $request, FormInterface $form) {
        if($request->getContentType() === 'json') {
            $form->submit($request->request->all());
        } else {
            $form->submit(array_merge($request->files->all(), $request->request->all()));
        }
    }

    /**
     * @param FormInterface $form
     * @return array
     */
    private function getErrorsFromForm(FormInterface $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }
        return $errors;
    }

}
