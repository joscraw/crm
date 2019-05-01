<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Entity\Folder;
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
use App\Repository\FolderRepository;
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
 * Class MoveListToFolderType
 * @package App\Form\Property
 */
class MoveListToFolderType extends AbstractType
{

    /**
     * @var FolderRepository
     */
    private $folderRepository;

    /**
     * MoveListToFolderType constructor.
     * @param FolderRepository $folderRepository
     */
    public function __construct(FolderRepository $folderRepository)
    {
        $this->folderRepository = $folderRepository;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $choices = [];

        /**
         * @var Portal $portal
         */
        $portal = $options['portal'];

        $folders = $this->folderRepository->findBy([
            'portal' => $portal->getId(),
            'type' => Folder::LIST_FOLDER
        ]);

        /*foreach($folders as $folder) {

            $choices[] = $folder->getName();
        }*/

        $builder->add('folder', ChoiceType::class, [
            'choices' => $folders,
            'label' => '',
            'required' => false,
            'expanded' => false,
            'multiple' => false,
            'placeholder' => 'Choose a folder or leave blank to move to the root folder.',
            'attr' => [
                'class' => 'js-selectize-single-select'
            ],
            'choice_label' => function ($choice, $key, $value) {

                return $choice->getName();

                // or if you want to translate some key
                //return 'form.choice.'.$key;
            },
            'choice_name' => function ($choice, $key, $value) {

                return $choice->getId();

                // or if you want to translate some key
                //return 'form.choice.'.$key;
            },
        ])->add('submit', SubmitType::class);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'portal'
        ]);
    }
}