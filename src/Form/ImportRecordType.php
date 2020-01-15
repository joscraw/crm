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
                new RecordImportSpreadsheet([])
            ]
        ]);
        $builder->get('file')->addEventListener(FormEvents::POST_SUBMIT, [$this, 'fieldModifier']);
    }

    /**
     * @see * @see https://stackoverflow.com/questions/25354806/how-to-add-an-event-listener-to-a-dynamically-added-field-using-symfony-forms#answer-29735470
     * @param FormEvent $event
     */
    public function fieldModifier(FormEvent $event) {
        $form = $event->getForm()->getParent();
        /** @var CustomObject $customObject */
        $customObject = $form->getConfig()->getOption('customObject');
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $event->getData();
        if(!$columns = $this->phpSpreadsheetHelper->getColumnNames($uploadedFile)) {
            return;
        }
        foreach($columns as $column) {
            $columnFormFieldName = $this->phpSpreadsheetHelper->formFriendly($column)[0];
            $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
                $columnFormFieldName,
                ChoiceType::class,
                null,
               [
                   'auto_initialize' => false,
                   'label' => false,
                   'choices'  => $this->phpSpreadsheetHelper->choicesForForm($column)
               ]
            );
            $form->add($builder->getForm());
            $properties = [];
            $properties['Unmapped'] = 'unmapped';
            foreach($customObject->getProperties() as $property) {
                $properties[$property->getLabel()] = $property->getInternalName();
            }
            $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
                $columnFormFieldName . '_properties',
                ChoiceType::class,
                null,
                [
                    'auto_initialize' => false,
                    'label' => false,
                    'choices'  => $properties,
                    'data' => 'unmapped'
                ]
            );
            $form->add($builder->getForm());
        }
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
        $resolver->setDefault('allow_extra_fields', true);
        $resolver->setRequired(['customObject']);
    }
}