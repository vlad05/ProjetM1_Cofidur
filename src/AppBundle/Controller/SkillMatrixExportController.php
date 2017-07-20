<?php
namespace AppBundle\Controller;

use PHPExcel_Cell;
use PHPExcel_Style_Fill;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use AppBundle\Form\Type\SkillMatrixType;

use AppBundle\Services\HistorisationSkillMatrixService;

class SkillMatrixExportController extends Controller
{

	public function skillMatrixExpAction(/*HistorisationSkillMatrixService $service*/) {
		//$response = $service->skillMatrix();
		return $this->container->get('app_cofidur.historisationskillmatrix');
	}

    public function skillMatrixExportAction(Request $request)
    {
		//"Import" du code depuis skillMatrixController.php : c'est du copié-collé mais ça serait mieux de faire un service
		/*******************/
		$em = $this->getDoctrine()->getManager();

		//traitement général
		$formations = $em->getRepository('AppBundle:Formation')->findAll();
		$operators= $em->getRepository('AppBundle:User')->findAll();
		$operatorsformations= $em->getRepository('AppBundle:OperatorFormation')->findAll();
		$allConnexions= [];

		$nbOperatorFormations = count($operatorsformations);


		/*************************/

		// ask the service for an excel object
		$phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

		$phpExcelObject->getProperties()->setCreator("PhpExcel")
		   ->setLastModifiedBy("Creator")
		   ->setTitle("Matrice des FFO")
		   ->setDescription("Document generated using PHP classes.")
		   ->setKeywords("office 2005 openxml php")
		   ->setCategory("MatriceFFO");


		/**************************************************************************/
		/*
		 * Copié-collé de code de SkillMatrixController (pasbien : ça serait mieux de faire un service)
		 */
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
			$phpExcelObject->setActiveSheetIndex(0)
			->setCellValue('A1', 'Matricule')
			->setCellValue('B1', 'Nom')
			->setCellValue('C1', 'Prénom')
			->setCellValue('D1', 'N+1')
			->setCellValue('E1', 'N+2')
			->setCellValue('F1', 'Statut');

            $nbOperatorFormations = count($operatorsformations);
            for ($i= 0; $i < $nbOperatorFormations+2; ++$i) {
                $operatorId= $operatorsformations[$i]->getOperator()->getId();
                $formationId= $operatorsformations[$i]->getFormation()->getId();
                $validation= $operatorsformations[$i]->getValidation();
                $operatorsformationsId= $operatorsformations[$i]->getId();

                $phpExcelObject->setActiveSheetIndex(0)
				->setCellValue('A'.$i+2, $operatorId)
				->setCellValue('B'.$i+2, $formationId)
				->setCellValue('C'.$i+2, $validation)
				->setCellValue('D'.$i+2, $operatorsformationsId);
            }
        }else{



			$phpExcelObject->setActiveSheetIndex(0)
			->setCellValue('A1', 'Matricule')
			->setCellValue('B1', 'Nom')
			->setCellValue('C1', 'Prénom')
			->setCellValue('D1', 'N+1')
			->setCellValue('E1', 'N+2')
			->setCellValue('F1', 'Statut');

			/* Boucle sur le nombre de formations pour les afficher en colonne */
			$nbFormations = count($formations);
			for($j = 0; $j < $nbFormations; ++$j) {
				$phpExcelObject->setActiveSheetIndex(0)
					->setCellValueByColumnAndRow($j+6, 1, $formations[$j]->getName());
			}

            $nbOperatorFormations = count($operatorsformations);
            $nbOperators = count($operators);
            /* Cette boucle sur le nombre d'opérateurs remplit les colonnes A à F du fichier avec l'id
             * le first name, last name, les supérieurs et le status. */
            for ($i= 0; $i < $nbOperators; ++$i) {
                $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A'.strval($i+2), $operators[$i]->getID())
				->setCellValue('B'.strval($i+2), $operators[$i]->getLastName())
				->setCellValue('C'.strval($i+2), $operators[$i]->getFirstName())
				->setCellValue('D'.strval($i+2), $operators[$i]->getSuperiorLvl1())
				->setCellValue('E'.strval($i+2), $operators[$i]->getSuperiorLvl2());
				switch($operators[$i]->getStatus()) {
					case 1 : // Iterim
						$phpExcelObject->setActiveSheetIndex(0)->setCellValue('F'.strval($i+2), "Iterim");
						break;
					case 2 : // CDD
						$phpExcelObject->setActiveSheetIndex(0)->setCellValue('F'.strval($i+2), "CDD");
						break;
					case 3 : // CDI
						$phpExcelObject->setActiveSheetIndex(0)->setCellValue('F'.strval($i+2), "CDI");
						break;
				}
				//->setCellValue('B'.strval($i+2), $operatorsformations[$i]->getFormation())
				//->setCellValue('C'.strval($i+2), $validation)
				//->setCellValue('D'.strval($i+2), $operatorsformationsId);
            }

			/* Boucle sur le nombre d'opérateurs en formation pour remplir le reste de la matrice */
			/* + boucle sur le nombre de formations
			 * + set des couleurs de la matrice en fonction de la valeur de la case (switch) */
			for($i = 0; $i < $nbOperatorFormations; ++$i) {
				$operatorId= $operatorsformations[$i]->getOperator()->getId();
				$formationId= $operatorsformations[$i]->getFormation()->getId();
				$validation= $operatorsformations[$i]->getValidation();
				$operatorsformationsId= $operatorsformations[$i]->getId();

				//Colonne de départ (sera incrémentée)
				$colG = 'G';
				$currColIndex = PHPExcel_Cell::columnIndexFromString($colG);
				$newIndex = $currColIndex;
				$currCol = $colG;

				//Boucle sur les colonnes
				for($j=0; $j<$nbFormations; ++$j) {
					//Si la formation de l'opérateur est égale au nom de la formation (en colonne) de la matrice, alors on remplit la valeur
					if($operatorsformations[$i]->getFormation()->getName() == $phpExcelObject->getActiveSheet()->getCellByColumnAndRow($j+6, 1)->getValue()) {

						//Inscrit la valeur de la validation dans la case correspondante
						$phpExcelObject->setActiveSheetIndex(0)
							->setCellValueByColumnAndRow($j+6, $operatorId+1, $operatorsformations[$i]->getValidation());

						//switch de coloriage de la case actuelle
						switch($operatorsformations[$i]->getValidation()) {
							case 1:	//Formé non habilité (orange)
								$phpExcelObject->getActiveSheet()
									->getStyle($currCol.strval($operatorId+1))
									->applyFromArray(
										array(
											'fill' => array(
												'type' => PHPExcel_Style_Fill::FILL_SOLID,
												'color' => array('rgb' => 'e28400')
											)
										)
									);
								break;
							case 2:	//En formation	(rouge)
								$phpExcelObject->getActiveSheet()
									->getStyle($currCol.strval($operatorId+1))
									->applyFromArray(
										array(
											'fill' => array(
												'type' => PHPExcel_Style_Fill::FILL_SOLID,
												'color' => array('rgb' => 'e20b0b')
											)
										)
									);
								break;
							case 3: //Prévision formation (bleu)
								$phpExcelObject->getActiveSheet()
									->getStyle($currCol.strval($operatorId+1))
									->applyFromArray(
										array(
											'fill' => array(
												'type' => PHPExcel_Style_Fill::FILL_SOLID,
												'color' => array('rgb' => '0059ad')
											)
										)
									);
								break;
							case 4:	//habilité	(vert)
								$phpExcelObject->getActiveSheet()
									->getStyle($currCol.strval($operatorId+1))
									->applyFromArray(
										array(
											'fill' => array(
												'type' => PHPExcel_Style_Fill::FILL_SOLID,
												'color' => array('rgb' => '00ad14')
											)
										)
									);
								break;
							case 5:	//habilité à former (jaune)
								$phpExcelObject->getActiveSheet()
									->getStyle($currCol.strval($operatorId+1))
									->applyFromArray(
										array(
											'fill' => array(
												'type' => PHPExcel_Style_Fill::FILL_SOLID,
												'color' => array('rgb' => 'e9ff00')
											)
										)
									);
								break;
							case 6:	//rétrogradé (gris)
								$phpExcelObject->getActiveSheet()
									->getStyle($currCol.strval($operatorId+1))
									->applyFromArray(
										array(
											'fill' => array(
												'type' => PHPExcel_Style_Fill::FILL_SOLID,
												'color' => array('rgb' => '959595')
											)
										)
									);
								break;
						}
					}
					++$newIndex;
					$currCol = PHPExcel_Cell::stringFromColumnIndex($newIndex -1);
				}
			}


        }

        /*************************************************************************/


		$phpExcelObject->getActiveSheet()->setTitle('Matrice de compétences');
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$phpExcelObject->setActiveSheetIndex(0);

		// create the writer
		$writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
		// create the response
		$response = $this->get('phpexcel')->createStreamedResponse($writer);
		// adding headers
		$dispositionHeader = $response->headers->makeDisposition(
			ResponseHeaderBag::DISPOSITION_ATTACHMENT,
			'MatriceFFO.xlsx'
		);
		$response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
		$response->headers->set('Pragma', 'public');
		$response->headers->set('Cache-Control', 'maxage=1');
		$response->headers->set('Content-Disposition', $dispositionHeader);

		return $response;
    }

}

