<?php
namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\ImportSalaries;
use AppBundle\Form\Type\OperatorType;
use AppBundle\Form\Type\ImportSalariesType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;

use PHPExcel\IOFactory;
use DateTime;

class OperatorController extends Controller
{

    public function showAction($idOp)
    {
        $em = $this->getDoctrine()->getManager();

        $operator = $em->getRepository('AppBundle:User')->find($idOp);

        if (!$operator) {
            throw $this->createNotFoundException('Pas d\'opérateur trouvé');
        }

        $operatorsFormations= $em->getRepository('AppBundle:OperatorFormation')->findBy(array('operator' => $operator));
        $supervisedFormations= $em->getRepository('AppBundle:OperatorFormation')->findBy(array('former' => $operator));
        $subordinates= $em->getRepository('AppBundle:User')->findBy(array('superiorLvl1' => $operator));

        return $this->render('AppBundle:Page/Operator:operator_show.html.twig', array(
            'operator'     => $operator,
            'operatorsformations' => $operatorsFormations,
            'supervisedFormations' => $supervisedFormations,
            'subordinates' => $subordinates
        ));
    }

    public function showAllAction()
    {
        $em = $this->getDoctrine()->getRepository('AppBundle:User');

        $operators = $em->findAll();

        return $this->render('AppBundle:Page/Operator:operator_show_all.html.twig', array(
            'operators'      => $operators,
        ));
    }

    public function deleteAction($idOp)
    {
        $em = $this->getDoctrine()->getManager();

        $operator = $em->getRepository('AppBundle:User')->find($idOp);

        if (!$operator) {
            throw $this->createNotFoundException('Pas d\'objet');
        }

        $em->remove($operator);
        $em->flush();

        return $this->redirectToRoute('AppBundle_operator_show_all');
    }

    public function setAdminAction($idOp) {

        $em = $this->getDoctrine()->getManager();

        $operator = $em->getRepository('AppBundle:User')->find($idOp);

        $operator->addRole("ROLE_ADMIN");
        $em->persist($operator);
        $em->flush();

        return $this->redirectToRoute('AppBundle_operator_show', array('idOp' => $operator->getId()));
    }

    public function unsetAdminAction($idOp) {

        $em = $this->getDoctrine()->getManager();

        $operator = $em->getRepository('AppBundle:User')->find($idOp);

        $operator->removeRole("ROLE_ADMIN");
        $em->persist($operator);
        $em->flush();

        return $this->redirectToRoute('AppBundle_operator_show', array('idOp' => $operator->getId()));
    }

    public function editAction(Request $request, $idOp)
    {
        $em = $this->getDoctrine()->getManager();

        $operator = $em->getRepository('AppBundle:User')->find($idOp);

        $form = $this->createForm(OperatorType::class, $operator);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $operator = $form->getData();

            $em->persist($operator);
            $em->flush();

            return $this->redirectToRoute('AppBundle_operator_show_all');
        }

        return $this->render('AppBundle:Page/Operator:operator_edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function addAction(Request $request)
    {
        $operator = new User();

        $form = $this->createForm(OperatorType::class, $operator);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $operator = $form->getData();
            $operator->setUsername(strtolower(substr($operator->getFirstName(), 0, 3)) . strtolower($operator->getLastName()));
            $random = random_bytes(10);
            $operator->setPassword(base64_encode($random));

            $em = $this->getDoctrine()->getManager();
            $em->persist($operator);
            $em->flush();

            $nextAction= $form->get('saveAndAdd')->isClicked()
                ? 'AppBundle_operator_add'
                : 'AppBundle_operator_show_all';

            return $this->redirectToRoute($nextAction);
        }

        return $this->render('AppBundle:Page/Operator:operator_add.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    
    //Raz du mot de passe (On le fixe à la valeur $operator->getFirstName() : à l'utilisateur de rechanger derrière)
    public function razAction($idOp)
    { //http://stackoverflow.com/questions/9183368/symfony2-user-setpassword-updates-password-as-plain-text-datafixtures-fos
		
		$em = $this->getDoctrine()->getManager();

        $operator = $em->getRepository('AppBundle:User')->find($idOp);
		
		$userManager = $this->container->get('fos_user.user_manager');
		$operator->setPlainPassword($operator->getFirstName());
		$userManager->updateUser($operator, false);
		
		$em->persist($operator);
		$em->flush();
		
        return $this->redirectToRoute('AppBundle_operator_show_all');
	}
    
    //Sert à l'import de tous les salariés à partir d'un fichier PRN (qui vient d'horoquartz)
    public function importAction(Request $request)
    {
		$fichier = new ImportSalaries();
		
	//Code pour faire un upload de fichier : unecessary (fin du code en bas de fonction)
		//$form = $this->createForm(ImportSalariesType::class, $fichier);
	
        /*$form = $this->createFormBuilder($fichier)
            ->add('importSalaries', FileType::class, array('label' =>'Fichier à importer (.prn only)'));            
		
		/*return $this->render('AppBundle:Page/Operator:operator_show_all.html.twig', array(
            'form' => $form->createView(),
        ));
		*/
		//$form->handleRequest($request);
		
		//https://symfony.com/doc/current/controller/upload_file.html#main
		/*if ($form->isSubmitted() && $form->isValid()) {
			return $this->redirectToRoute('AppBundle_homepage');
            // $file stores the uploaded PDF file
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
          //  $file = $fichier->getFichier();

            // Generate a unique name for the file before saving it
           
          // $fileName = md5(uniqid()).'.'.$file->guessExtension();

            // Move the file to the directory where brochures are stored
           /* $file->move(
                $this->getParameter('fichierimport_directory'),
                $fileName
            );

            // Update the 'brochure' property to store the PDF file name
            // instead of its contents
            $product->setImportSalaries($fileName);
			*/
/*************************************************************/
			$row = 1;
			$em = $this->getDoctrine()->getManager();
			
			if (($handle = fopen("/var/www/html/cofidur_projet/salariesTXT.prn", "r")) !== FALSE) {
				while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) { 
					$num = count($data);
					$row++;
					if($row > 10) {
						//Création d'un nouvel opérateur
						$operator = new User();

						$valeurs = trim($data[0]);

						$mots = preg_replace('/\s\s+/', ' ', $valeurs);
						$mots = explode(' ', $mots);

						//Boucle d'affichage pour voir si la chaîne s'est bien divisée en tableau
						for ($x=0; $x < count($mots); $x++) {
							echo $x . " - " . $mots[$x] . "<br />\n";
						}

						$nomNPlus1 = $em->getRepository('AppBundle:User')
							->findOneBy(array('lastName' => $mots[6]));
						$nomNPlus2 = $em->getRepository('AppBundle:User')
							->findOneBy(array('lastName' => $mots[7]));
						
						//var_dump($nomNPlus1);
						
						//test
						if(!$nomNPlus1) 
							echo "Nop : ".$mots[6]."<br />";
						
						//parser le string en date
						$dateEntree = date_parse($mots[5]);
						//print_r($dateEntree);
						
						$mots5 = str_replace('/', '-', $mots[5]);
						$date = DateTime::createFromFormat('j-m-Y', $mots5);
						//var_dump($date);
						
						echo "<br /> coucou - ". strval($mots[0]) . "<br />";
						
						switch($mots[4]) {
							case "CDI":
								$mots[4] = 1;
							case "CDD":
								$mots[4] = 2;
						}
						
						$operator->setRegistrationNumber(strval($mots[0]))
							->setLastName($mots[1])
							->setFirstName($mots[2])
							->setSite($mots[3])
							->setStatus($mots[4])
							->setDateEntree($date)
							->setSuperiorLvl1($nomNPlus1)
							->setSuperiorLvl2($nomNPlus2);
						$operator->setUsername(strtolower(substr($operator->getFirstName(), 0, 3)) . strtolower($operator->getLastName()));
						$random = random_bytes(10);
						$operator->setPassword(base64_encode($random));
						
						
						$em = $this->getDoctrine()->getManager();
						$em->persist($operator);
						
				    }
				}
				//en dehors de la boucle car coûteux
				$em->flush();
				
				
				fclose($handle);
			}
		

/*************************************************************/

	//Code pour faire un upload de fichier : unecessary
            // ... persist the $product variable or any other work

            //return $this->redirect($this->generateUrl('app_product_list'));
        //}	//fin if($form->isSubmitted() && $form->isValid())

     /*   return $this->render('AppBundle:Page/Operator:operator_show_all.html.twig', array(
            'form' => $form->createView(),
        ));
		*/
		return $this->redirectToRoute('AppBundle_operator_show_all');
		
	}

}
