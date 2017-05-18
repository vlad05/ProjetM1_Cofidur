<?php

namespace AppBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ImportSalariesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('importSalaries', FileType::class, array('label' =>'Fichier à importer (.prn only)'));
            
    }
    
        public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\ImportSalaries',
        ));
    }

//    public function getParent()
//    {
////        return 'AppBundle\Form\Type\RegistrationType';
//        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
//    }

}
