<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Model\CustomObjectField;
use App\Model\DatePickerField;
use App\Model\DropdownSelectField;
use App\Model\SingleLineTextField;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DatePickerFieldType
 * @package App\Form
 */
class CustomObjectFieldType extends AbstractType
{

    /**
     * @var CustomObjectRepository
     */
    private $customObjectRepository;

    /**
     * @var PropertyRepository
     */
    private $propertyRepository;

    /**
     * CustomObjectFieldType constructor.
     * @param CustomObjectRepository $customObjectRepository
     * @param PropertyRepository $propertyRepository
     */
    public function __construct(CustomObjectRepository $customObjectRepository, PropertyRepository $propertyRepository)
    {
        $this->customObjectRepository = $customObjectRepository;
        $this->propertyRepository = $propertyRepository;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Portal $portal */
        $portal = $options['portal'];

        $builder->add('customObject', EntityType::class, array(
            'class' => CustomObject::class,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('customObject')
                    ->orderBy('customObject.label', 'ASC');
            },
            'choice_label' => 'label',
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'placeholder' => false,
            'attr' => [
                'class' => 'js-custom-object'
            ]
        ));

        $builder->add('multiple', CheckboxType::class, array(
            'label'    => 'Multiple',
            'required' => false,
        ));

        $formModifier = function (FormInterface $form, CustomObjectField $customObjectField = null) use ($portal) {

            $form->add('selectizeSearchResultProperties', EntityType::class, array(
                'class' => Property::class,
                'query_builder' => function (EntityRepository $er) use ($portal) {
                    return $this->getQueryBuilder($portal);
                },
                'choice_label' => 'label',
                'expanded' => false,
                'multiple' => true,
                'required' => true,
                /*'data' => 60,*/
                /*'placeholder' => false,*/
                'attr' => [
                    'placeholder' => 'Start typing to search..',
                    'class' => 'js-custom-object'
                ]
            ));



           /* $positions = null === $sport ? array() : $sport->getAvailablePositions();*/

           /* $form->add('selectizeSearchResultProperties',
                TextType::class,
                [
                    'auto_initialize' => false
                ]
            );*/
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                // this would be your entity, i.e. SportMeetup
                $data = $event->getData();

                if($data) {

                }

                $formModifier($event->getForm());
            }
        );

        /*$builder->get('customObject')->addEventListener(FormEvents::POST_SUBMIT, [$this, 'fieldModifier']);*/

/*        $builder->get('sport')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                // It's important here to fetch $event->getForm()->getData(), as
                // $event->getData() will get you the client data (that is, the ID)
                $sport = $event->getForm()->getData();

                // since we've added the listener to the child, we'll have to pass on
                // the parent to the callback functions!
                $formModifier($event->getForm()->getParent(), $sport);
            }
        );*/

/*        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                // this would be your entity, i.e. SportMeetup
                $data = $event->getData();

                $formModifier($event->getForm(), $data->getSport());
            }
        );


        $builder->get('customObject')->addEventListener(FormEvents::POST_SUBMIT, [$this, 'fieldModifier']);*/

        /*$builder->add('selectizeSearchResultProperties', CheckboxType::class, array(
            'label'    => 'Multiple',
            'required' => false,
        ));

        $choices = $property->getField()->getOptionsForChoiceTypeField();
        $options = array_merge([
            'choices'  => $choices,
            'label' => $property->getLabel(),
            'expanded' => false,
            'multiple' => true,
            'required' => false,
            'attr' => [
                'class' => 'js-selectize-multiple-select'
            ]
        ], $options);
        $builder->add($property->getInternalName(), ChoiceType::class, $options);*/
    }

    private function getQueryBuilder(Portal $portal) {

        return $this->propertyRepository->createQueryBuilder('property')
            ->innerJoin('property.customObject', 'customObject')
            ->where('customObject.portal = :portal')
            ->setParameter('portal', $portal);
    }

    /**
     * @see * @see https://stackoverflow.com/questions/25354806/how-to-add-an-event-listener-to-a-dynamically-added-field-using-symfony-forms#answer-29735470
     * @param FormEvent $event
     */
    public function fieldModifier(FormEvent $event) {

        // since we've added the listener to the child, we'll have
        // to grab the parent
        $form = $event->getForm()->getParent();
        $data = $event->getData();


        if(is_array($data)) {
            $name = "josh";
        }

        // This is a really important thing to NOTE!
        // event listeners can only be attached to a builder (FormBuilderInterface)
        // and NOT to a form (FormInterface). This is why we have to create our own builder
        // the builder is nothing more then a form field
        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
            'selectizeSearchResultProperties',
            TextType::class,
            null,
            [
                'auto_initialize' => false
            ]
        );

        // last but not least, let's add the builder (form field) to the main form
        $form->add($builder->getForm());
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CustomObjectField::class,
        ));

        $resolver->setRequired([
            'portal'
        ]);
    }
}