<?php

namespace App\Form;

use App\Entity\Portal;
use App\Entity\Role;
use App\Repository\CustomObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
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

        return Role::$permissions;
    }
}