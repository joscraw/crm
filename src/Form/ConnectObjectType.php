<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\Record;
use App\Form\DataTransformer\IdArrayToRecordArrayTransformer;
use App\Form\DataTransformer\IdToRecordTransformer;
use App\Form\DataTransformer\RecordCheckboxTransformer;
use App\Form\DataTransformer\RecordDateTimeTransformer;
use App\Form\DataTransformer\RecordGenericTransformer;
use App\Form\DataTransformer\RecordNumberCurrencyTransformer;
use App\Form\DataTransformer\RecordNumberUnformattedTransformer;
use App\Model\DatePickerField;
use App\Model\FieldCatalog;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Class ConnectObjectType
 * @package App\Form\Property
 */
class ConnectObjectType extends AbstractType
{

    /**
     * @var PropertyGroupRepository
     */
    private $propertyGroupRepository;

    /**
     * @var PropertyRepository
     */
    private $propertyRepository;

    /**
     * @var CustomObjectRepository
     */
    private $customObjectRepository;

    /**
     * ConnectObjectType constructor.
     * @param PropertyGroupRepository $propertyGroupRepository
     * @param PropertyRepository $propertyRepository
     * @param CustomObjectRepository $customObjectRepository
     */
    public function __construct(
        PropertyGroupRepository $propertyGroupRepository,
        PropertyRepository $propertyRepository,
        CustomObjectRepository $customObjectRepository
    ) {
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->propertyRepository = $propertyRepository;
        $this->customObjectRepository = $customObjectRepository;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws \Doctrine\DBAL\DBALException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $customObject = $options['customObject'];

        $customObjectRepository = $this->customObjectRepository;
        $propertyRepository = $this->propertyRepository;

        $connectableObjects = $customObjectRepository->getConnectableObjects($customObject);
        $firstConnectableObject = !empty($connectableObjects) ? $connectableObjects[0] : null;

        $builder->add('connectableObject', EntityType::class, [
            'class' => CustomObject::class,
            'query_builder' => function (EntityRepository $er) use($customObjectRepository, $customObject) {
                return $customObjectRepository->getConnectableObjects($customObject, true);
            },
            'label' => 'Select an object to connect.',
            'choice_label' => 'label',
            'attr' => [
                'class' => 'js-selectize-single-select-connectable-object',

            ],
        ]);

        $builder->add('joinType', ChoiceType::class, [
            'label' => 'Select a join type.',
            'choices'  => [
                'With' => 'WITH',
                'Without' => 'WITHOUT',
                'With/Without' => 'WITH/WITHOUT',
            ],
            'attr' => [
                'class' => 'js-selectize-single-select',
            ]
        ]);

        $builder->add('propertyValue', EntityType::class,
            [
                'auto_initialize' => false,
                'class' => Property::class,
                'query_builder' => function (EntityRepository $er) use($propertyRepository, $customObject, $firstConnectableObject) {
                    return $propertyRepository->getConnectableProperties($customObject, $firstConnectableObject, true);
                },
                'label' => 'Property to join on.',
                'choice_label' => 'label',
                'attr' => [
                    'class' => 'js-selectize-single-select-property',

                ],
            ]);

        /*$builder->get('connectableObject')->addEventListener(FormEvents::POST_SUBMIT, [$this, 'fieldModifier']);*/


        $builder->get('connectableObject')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $customObject = $event->getForm()->getData();
            $this->modifyForm($event->getForm()->getParent(), $customObject);
        });
    }

    /**
     * @see * @see https://stackoverflow.com/questions/25354806/how-to-add-an-event-listener-to-a-dynamically-added-field-using-symfony-forms#answer-29735470
     * @param FormInterface $form
     * @param CustomObject|null $connectableCustomObject
     */
/*    public function fieldModifier(FormEvent $event) {

        // since we've added the listener to the child, we'll have
        // to grab the parent
        $form = $event->getForm()->getParent();
        $customObject = $form->getConfig()->getOption('customObject');
        $customObjectRepository = $this->customObjectRepository;
        $propertyRepository = $this->propertyRepository;
        $data = $event->getData();
        $j = $event->getForm()->getData();

        // This is a really important thing to NOTE!
        // event listeners can only be attached to a builder (FormBuilderInterface)
        // and NOT to a form (FormInterface). This is why we have to create our own builder
        // the builder is nothing more then a form field
        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
            'propertyValue',
            EntityType::class, null,
            [
                'auto_initialize' => false,
                'class' => Property::class,
                'query_builder' => function (EntityRepository $er) use($propertyRepository, $customObject) {
                    return $propertyRepository->getConnectableProperties($customObject, true);
                },
                'label' => 'Property to join on.',
                'choice_label' => 'label',
                'attr' => [
                    'class' => 'js-selectize-single-select-property',

                ],
            ]
        );

        // last but not least, let's add the builder (form field) to the main form
        $form->add($builder->getForm());

    }*/

    private function modifyForm(FormInterface $form, CustomObject $connectableCustomObject = null) {

        $data = $form->getData();
        $propertyRepository = $this->propertyRepository;
        $customObject = $form->getConfig()->getOption('customObject');

        $form->add('propertyValue', EntityType::class, [
            'auto_initialize' => false,
            'class' => Property::class,
            'query_builder' => function (EntityRepository $er) use($propertyRepository, $customObject, $connectableCustomObject) {
                return $propertyRepository->getConnectableProperties($customObject, $connectableCustomObject, true);
            },
            'label' => 'Property to join on.',
            'choice_label' => 'label',
            'attr' => [
                'class' => 'js-selectize-single-select-property',

            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'customObject'
        ]);
    }
}