<?php

namespace App\Form;

use App\Form\DataTransformer\IdArrayToRecordArrayTransformer;
use App\Form\DataTransformer\IdToRecordTransformer;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BulkEditCustomObjectFieldType
 * @package App\Form\Property
 */
class BulkEditCustomObjectFieldType extends AbstractType
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
     * @var IdToRecordTransformer
     */
    private $transformer;

    /**
     * @var IdArrayToRecordArrayTransformer
     */
    private $idArrayToRecordArrayTransformer;

    /**
     * BulkEditCustomObjectFieldType constructor.
     * @param RecordRepository $recordRepository
     * @param PropertyRepository $propertyRepository
     * @param IdToRecordTransformer $transformer
     * @param IdArrayToRecordArrayTransformer $idArrayToRecordArrayTransformer
     */
    public function __construct(
        RecordRepository $recordRepository,
        PropertyRepository $propertyRepository,
        IdToRecordTransformer $transformer,
        IdArrayToRecordArrayTransformer $idArrayToRecordArrayTransformer
    ) {
        $this->recordRepository = $recordRepository;
        $this->propertyRepository = $propertyRepository;
        $this->transformer = $transformer;
        $this->idArrayToRecordArrayTransformer = $idArrayToRecordArrayTransformer;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $property = $options['property'];

        if($property->getField()->isMultiple()) {
            $builder->addModelTransformer($this->idArrayToRecordArrayTransformer);
        } else {
            $builder->addModelTransformer($this->transformer);
        }
    }

    public function getParent()
    {
        return RecordChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['property']);

    }
}