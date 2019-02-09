<?php

namespace App\Form;

use App\Form\Type\NumberFormatType;
use App\Model\NumberField;
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
 * Class NumberFieldType
 * @package App\Form
 */
class NumberFieldType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $this->modifyForm($event->getForm());
        });
    }

    private function modifyForm(FormInterface $form) {

         $form->add('type', NumberFormatType::class, array(
             'choices' => NumberField::$types,
             'expanded' => true,
             'empty_data' => NumberField::$types['Unformatted Number']
          ));

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => NumberField::class,
        ));
    }
}