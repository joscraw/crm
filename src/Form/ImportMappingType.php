<?php

namespace App\Form;

use App\Form\EventType\EventImportMappingFileType;
use App\Validator\Constraints\RecordImportSpreadsheet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;


/**
 * Class ImportMappingType
 * @package App\Form\Property
 */
class ImportMappingType extends AbstractType
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * ImportMappingType constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
            $form = $event->getForm();
            $request = $this->requestStack->getCurrentRequest();
            if($request->getContentType() === 'json') {
                $form->add('file', EventImportMappingFileType::class, []);
            } else {
                $form->add('file', FileType::class, [
                    'multiple' => false,
                    'constraints' => [
                        new RecordImportSpreadsheet([]),
                        new NotNull([])
                    ]
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'allow_extra_fields' => true
        ]);
    }
}