<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class OperatorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, ['label_format' => 'operator.firstName',])
            ->add('lastName', TextType::class,  ['label_format' => 'operator.lastName',])
            ->add('registrationNumber', TextType::class,  ['label_format' => 'operator.registrationNumber', 'required' => true,])
            /*->add('dateOfBirth', DateType::class, array(
                'label_format' => 'security.login.dateOfBirth',
                'placeholder' => array(
                    'year' => 'date.year', 'month' => 'date.month', 'day' => 'date.day'
                ),
                // L'année de naissance peut être choisie entre 80 et 14 ans avant l'année actuelle
                'years' => range(date("Y",strtotime("-14 year")), date("Y",strtotime("-85 year")))
            ))*/
            ->add('email', EmailType::class,    ['label_format' => 'operator.email', 'required' => false,])
            ->add('status', ChoiceType::class,
                array(
                    'choices'  => array(
                        'operator.statusChoices.interim' => 1,
                        'operator.statusChoices.cdd' => 2,
                        'operator.statusChoices.cdi' => 3,
                    ),
                    'label_format' => 'operator.employmentStatus',
                )
            )
            ->add('superiorLvl1', EntityType::class,
                array(
                   'class'  => 'AppBundle:User', 'choice_label' => 'firstName',
                    'label_format' => 'operatorFormation.superior1Name',
                )
            )
            ->add('superiorLvl2', EntityType::class,
                array(
                    'class'  => 'AppBundle:User', 'choice_label' => 'firstName',
                    'label_format' => 'operatorFormation.superior2Name',
                )
            )
            ->add('site', ChoiceType::class,
				array(
					'choices' => array(
						'Périgueux' => 'operator.siteChoices.perigueux',
						'Laval' => 'operator.siteChoices.laval',
						'Cherbourg' => 'operator.siteChoices.cherbourg',
						'Montpellier' => 'operator.siteChoices.montpellier',
					),
				)
			)
			//A ajuster : la date d'entrée ne peut pas être supérieur à la date actuelle + 1 an
			->add('dateEntree', DateType::class, array(
                //'label_format' => 'security.login.dateEntree',
                'placeholder' => array(
                    'year' => 'date.year', 'month' => 'date.month', 'day' => 'date.day'
                ),
                'years' => range(date("Y",strtotime("1 year")), date("Y",strtotime("-40 year")))
            ))
            ->add('save', SubmitType::class, array('label' => 'operator.add.submit'))
            ->add('saveAndAdd', SubmitType::class, array('label' => 'operator.add.submitAndAdd'));
    }

    public function buildFormSorting(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, ['label_format' => 'operator.firstName',])
            ->add('lastName', TextType::class,  ['label_format' => 'operator.lastName',])
            ->add('registrationNumber', TextType::class,  ['label_format' => 'operator.registrationNumber', 'required' => false,])
            ->add('status', ChoiceType::class,
                array(
                    'choices'  => array(
                        'operator.statusChoices.interim' => 1,
                        'operator.statusChoices.cdd' => 2,
                        'operator.statusChoices.cdi' => 3,
                    ),
                    'label_format' => 'operator.employmentStatus',
                )
            )
            ->add('superiorLvl1', EntityType::class,
                array(
                   'class'  => 'AppBundle:User', 'choice_label' => 'firstName',
                    'label_format' => 'operatorFormation.superior1Name',
                )
            )
            ->add('superiorLvl2', EntityType::class,
                array(
                    'class'  => 'AppBundle:User', 'choice_label' => 'firstName',
                    'label_format' => 'operatorFormation.superior2Name',
                )
            )
            ->add('site', ChoiceType::class,
				array(
					'choices' => array(
						'Périgueux' => 'operator.siteChoices.perigueux',
						'Laval' => 'operator.siteChoices.laval',
						'Cherbourg' => 'operator.siteChoices.cherbourg',
						'Montpellier' => 'operator.siteChoices.montpellier',
					),
				)
			)
            ->add('save', SubmitType::class, array('label' => 'operator.add.submit'));
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User',
        ));
    }

//    public function getParent()
//    {
////        return 'AppBundle\Form\Type\RegistrationType';
//        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
//    }

    public function getBlockPrefix()
    {
        return 'app_user_registration';
    }

    public function getFirstName()
    {
        return $this->getBlockPrefix();
    }

    public function getLastName()
    {
        return $this->getBlockPrefix();
    }

}
