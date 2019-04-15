<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Entity\Property;
use App\Entity\Record;
use App\Form\DataTransformer\IdArrayToRecordArrayTransformer;
use App\Form\DataTransformer\RecordCheckboxTransformer;
use App\Form\DataTransformer\RecordGenericTransformer;
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