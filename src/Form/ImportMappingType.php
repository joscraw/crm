<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Form\DataTransformer\ImportFileTransformer;
use App\Form\EventType\EventImportMappingFileType;
use App\Service\PhpSpreadsheetHelper;
use App\Validator\Constraints\RecordImportSpreadsheet;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
     * @var PhpSpreadsheetHelper
     */
    private $phpSpreadsheetHelper;

    /**
     * @var ImportFileTransformer;
     */
    private $importFileTransformer;

    /**
     * ImportMappingType constructor.
     * @param RequestStack $requestStack
     * @param PhpSpreadsheetHelper $phpSpreadsheetHelper
     * @param ImportFileTransformer $importFileTransformer
     */
    public function __construct(
        RequestStack $requestStack,
        PhpSpreadsheetHelper $phpSpreadsheetHelper,
        ImportFileTransformer $importFileTransformer
    ) {
        $this->requestStack = $requestStack;
        $this->phpSpreadsheetHelper = $phpSpreadsheetHelper;
        $this->importFileTransformer = $importFileTransformer;
    }


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Portal $portal */
        $portal = $options['portal'];

        $builder->add('customObject', EntityType::class, array(
            'required' => true,
            'placeholder' => false,
            'class' => CustomObject::class,
            'query_builder' => function (EntityRepository $er) use ($portal) {
                return $er->createQueryBuilder('customObject')
                    ->where('customObject.portal = :portal')
                    ->setParameter('portal', $portal);
            },
            'choice_label' => 'name',
        ));

        $builder->add('originalFileName', TextType::class, array());

        $builder->add('file', TextareaType::class, []);

        $builder->get('file')->addEventListener(FormEvents::POST_SUBMIT, [$this, 'fieldModifier']);
        $builder->get('file')->addModelTransformer($this->importFileTransformer);
    }

    /**
     * @see * @see https://stackoverflow.com/questions/25354806/how-to-add-an-event-listener-to-a-dynamically-added-field-using-symfony-forms#answer-29735470
     * @param FormEvent $event
     * @throws \Exception
     */
    public function fieldModifier(FormEvent $event) {
        $form = $event->getForm()->getParent();
        /** @var CustomObject $customObject */
        $customObject = $form->get('customObject')->getData();
        /** @var File $uploadedFile */
        $uploadedFile  = $event->getForm()->getData();

        if(empty($uploadedFile)) {
            return;
        }

        try {
            $columns = $this->phpSpreadsheetHelper->getColumns($uploadedFile);
        } catch (\Exception $exception) {
            return;
        }

        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
            'mappings',
            CollectionType::class,
            null,
            [
                'auto_initialize' => false,
                'entry_type'   => ImportRecordsColumnMappingType::class,
                'allow_add' => true,
                'label' => false,
                'error_bubbling' => false,
                'prototype' => true,
                'prototype_name' => '__prototype_one__',
                'entry_options' => [
                    'customObject' => $customObject,
                    'columns' => $columns
                ],
                /*'constraints' => [
                    new Count(['min' => 1, 'minMessage' => 'You must add at least one mapping!'])
                ]*/
            ]
        );
        $form->add($builder->getForm());

        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
            'import',
            SubmitType::class,
            null,
            [
                'auto_initialize' => false,
                'attr' => [
                    'class' => 'js-bulk-edit-update-button btn btn-primary btn-block'
                ]
            ]
        );
        $form->add($builder->getForm());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'allow_extra_fields' => true
        ]);

        $resolver->setRequired(['portal']);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}