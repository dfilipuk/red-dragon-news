<?php

namespace AppBundle\Form;

use AppBundle\Entity\Category;
use AppBundle\Validation\NewCategoryValidationGroupResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryNewType extends AbstractType
{
    private $groupResolver;

    public function __construct(NewCategoryValidationGroupResolver $groupResolver)
    {
        $this->groupResolver = $groupResolver;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [])
            ->add('parentName', TextType::class, [
                'required' => false
            ])
            ->add('isRootCategory', CheckboxType::class, [
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Category::class,
            'validation_groups' => $this->groupResolver
        ));
    }
}