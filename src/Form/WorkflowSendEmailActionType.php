<?php

namespace App\Form;

use App\Entity\WorkflowSendEmailAction;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkflowSendEmailActionType extends AbstractType
{
    protected $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, []);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $csrfProtection = true;
        $request = $this->requestStack->getCurrentRequest();
        $acceptHeader = AcceptHeader::fromString($request->headers->get('Accept'));
        if ($acceptHeader->has('application/json')) {
            $csrfProtection = false;
        }

        $resolver->setDefaults([
            'data_class' => WorkflowSendEmailAction::class,
            'csrf_protection' => $csrfProtection,
            'allow_extra_fields' => true
        ]);
    }
}
