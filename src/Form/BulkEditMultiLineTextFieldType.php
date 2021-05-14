<?php

namespace App\Form;

use App\Form\DataTransformer\RecordGenericTransformer;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BulkEditMultiLineTextFieldType
 * @package App\Form\Property
 */
class BulkEditMultiLineTextFieldType extends AbstractType
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
     * BulkEditPropertyType constructor.
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
        $builder->addModelTransformer($this->recordGenericTransformer);

    }

    public function getParent()
    {
        return TextareaType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['property']);

    }
}