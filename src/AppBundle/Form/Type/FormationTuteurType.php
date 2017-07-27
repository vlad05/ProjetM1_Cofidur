<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class FormationTuteurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tuteur', EntityType::class,
                array(
                   'class'  => 'AppBundle:User', 'choice_label' => 'lastNameFirstName',
                   'label_format' => 'formation.tutors',
                   //'mapped' => false,
                )
            )
            ->add('save', SubmitType::class, ['label_format' => 'formation.save.submit'])
            ->add('saveAndAdd', SubmitType::class,    ['label_format' => 'formation.save.submitAndAdd']);;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Formation',
        ));
    }
}
