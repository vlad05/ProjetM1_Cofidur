<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\Type\SkillMatrixType;


class SkillMatrixController extends Controller
{

    public function skillMatrixAction(Request $request)
    {

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
            //traitement général (c-à-d si le formulaire de tri n'a pas été utilisé)
            $formations = $em->getRepository('AppBundle:Formation')->findAll();
            $operators= $em->getRepository('AppBundle:User')->findAll();
            $operatorsformations= $em->getRepository('AppBundle:OperatorFormation')->findAll();
            $allConnexions= [];

			/* ************* */
			/* Ces deux foreach font passer la FFO en "validé" si toutes les catégories sont validées
			 * (si les dates de signature de chaque catégories sont non-nulles)
			*/

			foreach($operatorsformations as $ffo) {
				$valid = 1; //Variable servant à détecter que toutes les catégories sont bien validées. Si une seule ne l'est pas, on passe la variable à 0
				$operatorCats = $ffo->getOperatorcategories();
				if($operatorCats != null) {
					foreach($operatorCats as $operatorCat) {
						if($operatorCat != null) {
							if($operatorCat->getDateSignature() == null)
							{ //Si une date est null (c-à-d qu'une catégorie n'est pas validée) alors on passe $valid à 0 (pour ne pas passer la FFO en "validée")
								$valid = 0;
							}
						}
					}
					if($valid == 1) {	//Si $valid est toujours égale à 1 alors on set la validation à "Validée" : NE PAS OUBLIER DE PERSIST/FLUSH pour rentrer les changements en BDD
						$ffo->setValidation(4);
						$em->persist($ffo);
					}
				}
			}
			$em->flush();
			/* ************* */

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

		/* Ce petit morceau de code avant le return
		 * passe les formations de niveau 4 ayant une date de validité dépassée
		 * au statut "Rétrogradé"
		 */
		$operatorformationRetrogradation = $em->getRepository('AppBundle:OperatorFormation')->findAll();
		foreach($operatorformationRetrogradation as $operatorformationRetro) {
			if($operatorformationRetro->isInvalidated()) {
				$operatorformationRetro->setValidation(6);	//Statut "Rétrogradé"
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
