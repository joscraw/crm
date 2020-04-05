<?php

namespace App\Form;

use App\Form\DataTransformer\RecordGenericTransformer;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BulkEditRadioSelectFieldType
 * @package App\Form\Property
 */
class BulkEditRadioSelectFieldType extends AbstractType
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
     * @var RecordGenericTransformer
     */
    private $recordGenericTransformer;

    /**
     * BulkEditRadioSelectFieldType constructor.
     * @param RecordRepository $recordRepository
     * @param PropertyRepository $propertyRepository
     * @param RecordGenericTransformer $recordGenericTransformer
     */
    public function __construct(
        RecordRepository $recordRepository,
        PropertyRepository $propertyRepository,
        RecordGenericTransformer $recordGenericTransformer
    ) {
        $this->recordRepository = $recordRepository;
        $this->propertyRepository = $propertyRepository;
        $this->recordGenericTransformer = $recordGenericTransformer;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $property = $options['property'];

        $builder->addModelTransformer($this->recordGenericTransformer);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['property']);

    }
}