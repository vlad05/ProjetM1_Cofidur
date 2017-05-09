<?php 
namespace AppBundle\Services;

use Doctrine\ORM\EntityManager;

class SkillMatrixService
{
	private $em;
	
	public function __construct(EntityManager $entityManager) {
		$this->em = $entityManager;
	}
	
	
	public function skillMatrix() {
		//$em = $this->em->getDoctrine()->getManager();
		/*
        $form = $this->createForm(SkillMatrixType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //traitement spécifique au formulaire
            $allConnexions= [];

            $formationTabRecherche = [];
            $operatorTabRecherche = [];
            $operatorformationTabRecheche = [];

            //qualifications
            if($form["qualification"]->getData() != null){
                $operatorformationTabRecheche["validation"] = $form["qualification"]->getData();
            }

            //criticité
            if($form["criticality"]->getData() != null){
                $formationTabRecherche["criticality"] = $form["criticality"]->getData();
            }

            //statut
            if($form["status"]->getData() != null){
                $operatorTabRecherche["status"] = $form["status"]->getData();
            }

            //superieurlvl1
            if($form["superiorLvl1"]->getData() != null){
                $operatorTabRecherche["superiorLvl1"] = $form["superiorLvl1"]->getData();
            }

            //superieurlvl2
            if($form["superiorLvl2"]->getData() != null){
                $operatorTabRecherche["superiorLvl2"] = $form["superiorLvl2"]->getData();
            }

            //formation
            if($form["formation"]->getData() != null){
                $formationTabRecherche["name"] = $form["formation"]->getData()->getName();
            }


            //si les tab non vide
            if(count($operatorTabRecherche) != 0){
                $operators = $em->getRepository('AppBundle:User')->findBy($operatorTabRecherche);
            }else{
                $operators= $em->getRepository('AppBundle:User')->findAll();
            }

            if(count($formationTabRecherche) != 0){
                $formations=$em->getRepository('AppBundle:Formation')->findBy($formationTabRecherche);
            }else{
                $formations = $em->getRepository('AppBundle:Formation')->findAll();
            }

            if(count($operatorformationTabRecheche) != 0){
                $operatorsformations= $em->getRepository('AppBundle:OperatorFormation')->findBy($operatorformationTabRecheche);

                if($form["qualification"]->getData() != null){
                    $nbOpForm = count($operatorsformations);
                    for($i=0; $i<$nbOpForm; ++$i){

                        if($form["formation"]->getData() != null){
                            if($operatorsformations[$i]->getFormation() == $form["formation"]->getData()){
                                $new_Operators[$i] = $operatorsformations[$i]->getOperator();
                            }
                        }else{
                            $new_Operators[$i] = $operatorsformations[$i]->getOperator();
                        }

                    }


                    $end_Operators=[];
                    foreach($operators as $op){
                        foreach($new_Operators as $new_op){
                            if($op == $new_op)
                                $end_Operators[$op->getId()] = $op;
                        }
                    }

                    $operators = $end_Operators;
                }
            }else{
                $operatorsformations= $em->getRepository('AppBundle:OperatorFormation')->findAll();
            }

            //traitement
            $nbOperatorFormations = count($operatorsformations);
            for ($i= 0; $i < $nbOperatorFormations; ++$i) {
                $operatorId= $operatorsformations[$i]->getOperator()->getId();
                $formationId= $operatorsformations[$i]->getFormation()->getId();
                $validation= $operatorsformations[$i]->getValidation();
                $operatorsformationsId= $operatorsformations[$i]->getId();

                $allConnexions[$i]= [$operatorId, $formationId, $validation, $operatorsformationsId];
            }
        }else{*/
            //traitement général
            $formations = $em->getRepository('AppBundle:Formation')->findAll();
            $operators= $em->getRepository('AppBundle:User')->findAll();
            $operatorsformations= $em->getRepository('AppBundle:OperatorFormation')->findAll();
            $allConnexions= [];

            $nbOperatorFormations = count($operatorsformations);
            for ($i= 0; $i < $nbOperatorFormations; ++$i) {
                $operatorId= $operatorsformations[$i]->getOperator()->getId();
                $formationId= $operatorsformations[$i]->getFormation()->getId();
                $validation= $operatorsformations[$i]->getValidation();
                $operatorsformationsId= $operatorsformations[$i]->getId();

                $allConnexions[$i]= [$operatorId, $formationId, $validation, $operatorsformationsId];
            }
        //}

        return $this->render('AppBundle:Page:skillMatrix.html.twig', array(
            'form'                         => $form->createView(),
            'formations'                   => $formations,
            'operators'                    => $operators,
            'formationsValidation'         => $allConnexions,
        ));
	}
	
}



