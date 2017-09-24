<?php
/**
 * Created by PhpStorm.
 * User: Алексей
 * Date: 21.09.2017
 * Time: 20:30
 */

namespace AppBundle\Form;


use AppBundle\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;

class ArticleNewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('category', TextType::class, [
                'required' => true
            ])
            ->add('text', CKEditorType::class, [
                'config' => [
                    'uiColor' => '#ffffff',
                    'removeButtons' => 'Save,Print,Preview,Find,About,Maximize,ShowBlocks,NewPage,Templates,Replace,SelectAll,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,RemoveFormat,CopyFormatting,BidiLtr,BidiRtl,Link,Unlink,Anchor,CreatePlaceholder,Image,Flash,HorizontalRule,SpecialChar,PageBreak,Iframe,Language'
                ],
                'required' => true])
            ->add('picture', FileType::class, [
                'attr' => [
                    'class' => 'none',
                    'type' => 'file'
                ],
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Article::class,
        ));
    }
}