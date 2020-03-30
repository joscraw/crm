<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\Record;
use App\Form\DataTransformer\IdArrayToRecordArrayTransformer;
use App\Form\DataTransformer\IdToRecordTransformer;
use App\Form\DataTransformer\RecordCheckboxTransformer;
use App\Form\DataTransformer\RecordDateTimeTransformer;
use App\Form\DataTransformer\RecordGenericTransformer;
use App\Form\DataTransformer\RecordMultipleCheckboxTransformer;
use App\Form\DataTransformer\RecordNumberCurrencyTransformer;
use App\Form\DataTransformer\RecordNumberUnformattedTransformer;
use App\Model\DatePickerField;
use App\Model\FieldCatalog;
use App\Repository\RecordRepository;
use App\Service\ChunkReadFilter;
use App\Service\PhpSpreadsheetHelper;
use App\Validator\Constraints\RecordImportSpreadsheet;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;


/**
 * Class ImportRecordType
 * @package App\Form\Property
 */
class ImportRecordType extends AbstractType
{
    /**
     * @var PhpSpreadsheetHelper
     */
    private $phpSpreadsheetHelper;

    /**
     * ImportRecordType constructor.
     * @param PhpSpreadsheetHelper $phpSpreadsheetHelper
     */
    public function __construct(PhpSpreadsheetHelper $phpSpreadsheetHelper)
    {
        $this->phpSpreadsheetHelper = $phpSpreadsheetHelper;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', FileType::class, [
            'label' => 'Import file',
            'required' => true,
            'multiple' => false,
            'constraints' => [
                new RecordImportSpreadsheet(['groups' => ['MAPPING', 'IMPORT']])
            ]
        ]);

        $builder->get('file')->addEventListener(FormEvents::POST_SUBMIT, [$this, 'fieldModifier']);
    }

    /**
     * @see * @see https://stackoverflow.com/questions/25354806/how-to-add-an-event-listener-to-a-dynamically-added-field-using-symfony-forms#answer-29735470
     * @param FormEvent $event
     * @throws \Exception
     */
    public function fieldModifier(FormEvent $event) {
        $form = $event->getForm()->getParent();
        /** @var CustomObject $customObject */
        $customObject = $form->getConfig()->getOption('customObject');
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $event->getData();

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
                'prototype' => true,
                'prototype_name' => '__prototype_one__',
                'entry_options' => [
                    'customObject' => $customObject,
                    'columns' => $columns
                ],
                'constraints' => [
                    new Count(['min' => 1, 'minMessage' => 'You must add at least one mapping!', 'groups' => ['IMPORT']])
                ]
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
            'allow_extra_fields' => true,
            'csrf_protection' => false
        ]);
        $resolver->setRequired(['customObject']);
    }
}