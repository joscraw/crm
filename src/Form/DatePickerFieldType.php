<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Form\Type\DateFormatType;
use App\Model\DatePickerField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DatePickerFieldType
 * @package App\Form
 */
class DatePickerFieldType extends AbstractType
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

        $form->add('type', DateFormatType::class, array(
            'choices' => DatePickerField::$types,
            'expanded' => true,
            'empty_data' => DatePickerField::DATETIME
        ));

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => DatePickerField::class,
        ));
    }
}