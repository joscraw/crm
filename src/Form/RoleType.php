<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Entity\Role;
use App\Entity\User;
use App\Model\CustomObjectField;
use App\Model\FieldCatalog;
use App\Repository\CustomObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RoleType
 * @package App\Form\User
 */
class RoleType extends AbstractType
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var CustomObjectRepository
     */
    private $customObjectRepository;

    /**
     * RoleType constructor.
     * @param EntityManagerInterface $entityManager
     * @param CustomObjectRepository $customObjectRepository
     */
    public function __construct(EntityManagerInterface $entityManager, CustomObjectRepository $customObjectRepository)
    {
        $this->entityManager = $entityManager;
        $this->customObjectRepository = $customObjectRepository;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'required' => true,
                    'attr' => [
                        'autocomplete' => 'off'
                    ]
                ]
            );

        /*** @var Portal $portal */
        $portal = $options['portal'];

        $builder->add('objectPermissions', ChoiceType::class, [
            'required' => true,
            'choices' => $this->getObjectPermissions($portal),
            'expanded' => false,
            'multiple' => true,
            'attr' => [
                'class' => 'js-selectize-multiple-select',
                'autocomplete' => 'off'
            ]
        ]);

        $builder->add('systemPermissions', ChoiceType::class, [
            'required' => true,
            'choices' => $this->getSystemPermissions($portal),
            'expanded' => false,
            'multiple' => true,
            'attr' => [
                'class' => 'js-selectize-multiple-select',
                'autocomplete' => 'off'
            ]
        ]);

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Role::class,
        ));

        $resolver->setRequired('portal');

    }

    private function getObjectPermissions($portal) {

        $customObjects = $this->customObjectRepository->findBy(
            ['portal' => $portal],
            ['label' => 'ASC']
        );

        $permissions = [
            'ALL' => 'ALL'
        ];

        foreach($customObjects as $customObject) {
            $permissions[$customObject->getLabel()] = $customObject->getPermissions();
        }

        return $permissions;
    }

    private function getSystemPermissions($portal) {

        $permissions = [
            'ALL' => 'ALL',
            'REPORTS' => [
                'CREATE_REPORT' => 'CREATE_REPORT',
                'EDIT_REPORT' => 'EDIT_REPORT',
                'DELETE_REPORT' => 'DELETE_REPORT',
            ],
            'USERS' => [
                'CREATE_USER' => 'CREATE_USER',
                'EDIT_USER' => 'EDIT_USER',
                'DELETE_USER' => 'DELETE_USER',
            ],
            'ROLES' => [
                'CREATE_ROLE' => 'CREATE_ROLE',
                'EDIT_ROLE' => 'EDIT_ROLE',
                'DELETE_ROLE' => 'DELETE_ROLE',
            ],
            'CUSTOM OBJECTS' => [
                'CREATE_CUSTOM_OBJECT' => 'CREATE_CUSTOM_OBJECT',
                'EDIT_CUSTOM_OBJECT' => 'EDIT_CUSTOM_OBJECT',
                'DELETE_CUSTOM_OBJECT' => 'DELETE_CUSTOM_OBJECT',
            ],
            'PROPERTIES' => [
                'CREATE_PROPERTY' => 'CREATE_PROPERTY',
                'EDIT_PROPERTY' => 'EDIT_PROPERTY',
                'DELETE_PROPERTY' => 'DELETE_PROPERTY',
            ],
            'PROPERTY GROUPS' => [
                'CREATE_PROPERTY_GROUP' => 'CREATE_PROPERTY_GROUP',
                'EDIT_PROPERTY_GROUP' => 'EDIT_PROPERTY_GROUP',
                'DELETE_PROPERTY_GROUP' => 'DELETE_PROPERTY_GROUP',
            ]
        ];

        return $permissions;
    }
}