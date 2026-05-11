<?php

namespace App\Form\Admin;

use App\Entity\Cursus;
use App\Entity\Lesson;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LessonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom', 'constraints' => [new NotBlank()]])
            ->add('slug', TextType::class, ['label' => 'Slug (URL)', 'constraints' => [new NotBlank()]])
            ->add('price', MoneyType::class, ['label' => 'Prix (€)', 'currency' => 'EUR', 'divisor' => 1])
            ->add('position', IntegerType::class, ['label' => 'Position'])
            ->add('content', TextareaType::class, ['label' => 'Contenu', 'required' => false, 'attr' => ['rows' => 6]])
            ->add('videoUrl', UrlType::class, ['label' => 'URL vidéo', 'required' => false])
            ->add('cursus', EntityType::class, [
                'label' => 'Cursus',
                'class' => Cursus::class,
                'choice_label' => 'name',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Lesson::class]);
    }
}
