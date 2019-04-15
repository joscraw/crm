<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Entity\Property;
use App\Entity\Record;
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
 * Class BulkEditPropertyType
 * @package App\Form\Property
 */
class BulkEditPropertyType extends AbstractType
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
        $name = "Josh";
        /*$builder->addModelTransformer($this->recordGenericTransformer);*/

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $this->modifyForm($event->getForm());
        });

    }

    private function modifyForm(FormInterface $form) {

        $form->add('type', TextType::class, array(
            'constraints' => [
                new NotBlank(),
            ]
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['property']);

        /*$resolver->setDefaults([
            'constraints' => [
                new NotBlank(),
            ]
        ]);*/
    }
}