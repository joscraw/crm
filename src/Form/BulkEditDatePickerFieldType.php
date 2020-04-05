<?php

namespace App\Form;

use App\Form\DataTransformer\RecordDateTimeTransformer;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BulkEditDatePickerFieldType
 * @package App\Form\Property
 */
class BulkEditDatePickerFieldType extends AbstractType
{
    /**
     * @var RecordRepository
     */
    private $recordRepository;

    /**
     * @var PropertyRepository
     */
    private $propertyRepository;

    /**
     * @var RecordDateTimeTransformer
     */
    private $recordDateTimeTransformer;

    /**
     * BulkEditDatePickerFieldType constructor.
     * @param RecordRepository $recordRepository
     * @param PropertyRepository $propertyRepository
     * @param RecordDateTimeTransformer $recordDateTimeTransformer
     */
    public function __construct(
        RecordRepository $recordRepository,
        PropertyRepository $propertyRepository,
        RecordDateTimeTransformer $recordDateTimeTransformer
    ) {
        $this->recordRepository = $recordRepository;
        $this->propertyRepository = $propertyRepository;
        $this->recordDateTimeTransformer = $recordDateTimeTransformer;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $property = $options['property'];

        $builder->addModelTransformer($this->recordDateTimeTransformer);

    }

    public function getParent()
    {
        return DateType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['property']);

    }
}