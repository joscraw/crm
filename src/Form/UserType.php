<?php

namespace App\Form;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Entity\User;
use App\Model\CustomObjectField;
use App\Model\FieldCatalog;
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
 * Class UserType
 * @package App\Form\User
 */
class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('firstName', TextType::class, [
                'required' => true,
                'attr' => [
                    'autocomplete' => 'off'
                ]
            ])->add('lastName', TextType::class, [
                'required' => true,
                'attr' => [
                    'autocomplete' => 'off'
                ]
            ])->add('email', TextType::class, [
                'required' => true,
                'attr' => [
                    'autocomplete' => 'off'
                ]
            ])->add(
                'password',
                PasswordType::class
            )->add(
                'passwordRepeat',
                PasswordType::class
            )->add(
                'customRoles',EntityType::class, [
                    'required'     => true,
                    'class'        => 'App\Entity\Role',
                    'choice_label' => 'name',
                    'label'        => 'Roles',
                    'expanded'     => false,
                    'multiple'     => true,
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
            'data_class' => User::class,
            'validation_groups' => ['CREATE'],
        ));

    }
}