<?php

namespace App\Form\EventType;

use App\Form\DataTransformer\ImportFileTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventImportMappingFileType extends AbstractType
{
    /**
     * @var ImportFileTransformer;
     */
    private $transformer;

    /**
     * EventImportMappingFileType constructor.
     * @param ImportFileTransformer $transformer
     */
    public function __construct(ImportFileTransformer $transformer)
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
        return TextareaType::class;
    }
}