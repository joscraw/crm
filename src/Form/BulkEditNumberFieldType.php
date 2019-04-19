<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Entity\Property;
use App\Entity\Record;
use App\Form\DataTransformer\RecordGenericTransformer;
use App\Form\DataTransformer\RecordNumberCurrencyTransformer;
use App\Form\DataTransformer\RecordNumberUnformattedTransformer;
use App\Form\DataTransformer\SelectizeSearchResultPropertyTransformer;
use App\Form\DataTransformer\IdToRecordTransformer;
use App\Form\Loader\RecordChoiceLoader;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

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