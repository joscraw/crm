<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Utils\ServiceHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Class ApiController
 * @package App\Controller\Api
 *
 */
class ApiController extends AbstractController
{
    use ServiceHelper;

    public function getErrorsFromValidator(ConstraintViolationListInterface $validationErrors) {
        $errors = [];
        foreach ($validationErrors as $validationError) {
            $errors[$validationError->getPropertyPath()][] = $validationError->getMessage();
        }
        return $errors;
    }

    /**
     * @param Request $request
     * @param User $user
     * @return \App\Entity\Portal|null
     */
    public function getPortal(Request $request, User $user) {

        if($request->query->has('internalIdentifier')) {
            $portal = $this->portalRepository->findOneBy([
                'internalIdentifier' => $request->query->get('internalIdentifier')
            ]);

            if($portal) {
                return $portal;
            }
        }

        return $user->getPortal();
    }
}