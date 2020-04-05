<?php

namespace App\Form;

use App\Form\DataTransformer\RecordNumberCurrencyTransformer;
use App\Form\DataTransformer\RecordNumberUnformattedTransformer;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BulkEditNumberFieldType
 * @package App\Form\Property
 */
class BulkEditNumberFieldType extends AbstractType
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
     * @var RecordNumberCurrencyTransformer
     */
    private $recordNumberCurrencyTransformer;

    /**
     * @var RecordNumberUnformattedTransformer
     */
    private $recordNumberUnformattedTransformer;

    /**
     * BulkEditNumberFieldType constructor.
     * @param RecordRepository $recordRepository
     * @param PropertyRepository $propertyRepository
     * @param RecordNumberCurrencyTransformer $recordNumberCurrencyTransformer
     * @param RecordNumberUnformattedTransformer $recordNumberUnformattedTransformer
     */
    public function __construct(
        RecordRepository $recordRepository,
        PropertyRepository $propertyRepository,
        RecordNumberCurrencyTransformer $recordNumberCurrencyTransformer,
        RecordNumberUnformattedTransformer $recordNumberUnformattedTransformer
    ) {
        $this->recordRepository = $recordRepository;
        $this->propertyRepository = $propertyRepository;
        $this->recordNumberCurrencyTransformer = $recordNumberCurrencyTransformer;
        $this->recordNumberUnformattedTransformer = $recordNumberUnformattedTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $property = $options['property'];

        $field = $property->getField();
        if($field->isCurrency()) {
            $builder->addModelTransformer($this->recordNumberCurrencyTransformer);
        } else if($field->isUnformattedNumber()){
            $builder->addModelTransformer($this->recordNumberUnformattedTransformer);
        }


    }

    public function getParent()
    {
        return NumberType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['property']);

    }
}