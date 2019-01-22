<?php

namespace App\Form\Type;

use App\Form\DataTransformer\CustomTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SelectizeSearchResultPropertiesType extends AbstractType
{
    private $transformer;

    public function __construct(CustomTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}