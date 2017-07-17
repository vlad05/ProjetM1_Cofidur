<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\Type\SkillMatrixType;


class SkillMatrixController extends Controller
{

    public function skillMatrixAction(Request $request)
    {
        /* Test avec la classe en "service"
         *  $matrice = $this->container->get('app_cofidur.skillmatrix');

        return $matrice->skillMatrix();
        */
		$em = $this->getDoctrine()->getManager();

        $form = $this->createForm(SkillMatrixType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //traitement spécifique au formulaire
            $allConnexions= [];

            $formationTabRecherche = [];
            $operatorTabRecherche = [];
            $operatorformationTabRecheche = [];

            //Nom et prénom
            if($form["lastName"]->getData() != null){
                $operatorTabRecherche["lastName"] = $form["lastName"]->getData()->getLastName();
            }

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
				if(isset($operatorsformations[$i])) {
					if(!is_null($operatorsformations[$i]->getFormation()) && !is_null($operatorsformations[$i]->getOperator())) {
						$operatorId= $operatorsformations[$i]->getOperator()->getId();
						$formationId= $operatorsformations[$i]->getFormation()->getId();
						$validation= $operatorsformations[$i]->getValidation();
						$operatorsformationsId= $operatorsformations[$i]->getId();

						$allConnexions[$i]= [$operatorId, $formationId, $validation, $operatorsformationsId];
					}
				}
            }
        }else{
            //traitement général
            $formations = $em->getRepository('AppBundle:Formation')->findAll();
            $operators= $em->getRepository('AppBundle:User')->findAll();
            $operatorsformations= $em->getRepository('AppBundle:OperatorFormation')->findAll();
            $allConnexions= [];

            $nbOperatorFormations = count($operatorsformations);
            for ($i= 0; $i < $nbOperatorFormations; ++$i) {
				if(isset($operatorsformations[$i])) {
					if(!is_null($operatorsformations[$i]->getFormation()) && !is_null($operatorsformations[$i]->getOperator())) {
						$operatorId= $operatorsformations[$i]->getOperator()->getId();
						$formationId= $operatorsformations[$i]->getFormation()->getId();
						$validation= $operatorsformations[$i]->getValidation();
						$operatorsformationsId= $operatorsformations[$i]->getId();

						$allConnexions[$i]= [$operatorId, $formationId, $validation, $operatorsformationsId];
					}
				}
            }
        }

        return $this->render('AppBundle:Page:skillMatrix.html.twig', array(
            'form'                         => $form->createView(),
            'formations'                   => $formations,
            'operators'                    => $operators,
            'formationsValidation'         => $allConnexions,
        ));
    }

}
