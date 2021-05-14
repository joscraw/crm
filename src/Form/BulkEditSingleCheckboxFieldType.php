<?php

namespace App\Form;

use App\Form\DataTransformer\RecordCheckboxTransformer;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BulkEditSingleCheckboxFieldType
 * @package App\Form\Property
 */
class BulkEditSingleCheckboxFieldType extends AbstractType
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
     * @var RecordCheckboxTransformer
     */
    private $recordCheckboxTranformer;

    /**
     * BulkEditSingleCheckboxFieldType constructor.
     * @param RecordRepository $recordRepository
     * @param PropertyRepository $propertyRepository
     * @param RecordCheckboxTransformer $recordCheckboxTranformer
     */
    public function __construct(
        RecordRepository $recordRepository,
        PropertyRepository $propertyRepository,
        RecordCheckboxTransformer $recordCheckboxTranformer
    ) {
        $this->recordRepository = $recordRepository;
        $this->propertyRepository = $propertyRepository;
        $this->recordCheckboxTranformer = $recordCheckboxTranformer;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $property = $options['property'];

        $builder->addModelTransformer($this->recordCheckboxTranformer);
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