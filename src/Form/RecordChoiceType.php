<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Entity\Property;
use App\Entity\Record;
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

/**
 * Class RecordChoiceType
 * @package App\Form\Property
 * @see https://speakerdeck.com/heahdude/symfony-forms-use-cases-and-optimization?slide=43
 */
class RecordChoiceType extends AbstractType
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
     * RecordChoiceType constructor.
     * @param RecordRepository $recordRepository
     * @param PropertyRepository $propertyRepository
     * @param IdToRecordTransformer $transformer
     */
    public function __construct(
        RecordRepository $recordRepository,
        PropertyRepository $propertyRepository,
        IdToRecordTransformer $transformer
    ) {
        $this->recordRepository = $recordRepository;
        $this->propertyRepository = $propertyRepository;
        $this->transformer = $transformer;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*$builder->addModelTransformer($this->transformer);*/
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_loader' => function(Options $options) {

                return new RecordChoiceLoader($options, $this->recordRepository, $this->propertyRepository);
            },
            'multiple' => false
        ]);

        $resolver->setRequired(['property']);
    }
}