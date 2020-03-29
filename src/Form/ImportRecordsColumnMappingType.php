<?php

namespace App\Form;

use App\Entity\CustomObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * Class ImportRecordsColumnMappingType
 * @package App\Form\Property
 */
class ImportRecordsColumnMappingType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $columns = $options['columns'];
        /** @var CustomObject $customObject */
        $customObject = $options['customObject'];

        $builder->add('mapped_from',ChoiceType::class,
            [
                'auto_initialize' => false,
                'label' => false,
                'choices'  => array_combine($columns, $columns)
            ]);

        $properties = [];
        $properties['Unmapped'] = 'unmapped';
        foreach($customObject->getProperties() as $property) {
            $properties[$property->getLabel()] = $property->getInternalName();
        }

        $builder->add('mapped_to',ChoiceType::class,
            [
                'auto_initialize' => false,
                'label' => false,
                'choices'  => $properties,
                'data' => 'unmapped'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('allow_extra_fields', true);
        $resolver->setRequired(['customObject', 'columns']);
    }
}