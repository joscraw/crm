<?php

namespace App\Form\Type;

use App\Form\DataTransformer\SelectizeSearchResultPropertyTransformer;
use App\Model\NumberField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DateFormatType extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver)
    {
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}