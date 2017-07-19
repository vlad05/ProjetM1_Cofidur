<?php
namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\ImportSalaries;
use AppBundle\Form\Type\OperatorType;
use AppBundle\Form\Type\ImportSalariesType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;



use PHPExcel\IOFactory;
use DateTime;

class OperatorController extends Controller
{

	/*public function operatorAction(Request $request)
	{

	}*/

    public function showAction($idOp)
    { //Show un opérateur ainsi que ses formations en cours et les formations qu'il supervise
        $em = $this->getDoctrine()->getManager();

        $operator = $em->getRepository('AppBundle:User')->find($idOp);

        if (!$operator) {
            throw $this->createNotFoundException('Pas d\'opérateur trouvé');
        }

		//$this->container->get('security.role_hierarchy');
		//$this->get('AppBundle.service.role')->isGranted('ROLE_ADMIN', $operator);
		$role = array(new Role('ROLE_ADMIN'));
		$all_roles = $this->get('security.role_hierarchy')->getReachableRoles($role);

        $operatorsFormations= $em->getRepository('AppBundle:OperatorFormation')->findBy(array('operator' => $operator));
        $supervisedFormations= $em->getRepository('AppBundle:OperatorFormation')->findBy(array('former' => $operator));
        $subordinates= $em->getRepository('AppBundle:User')->findBy(array('superiorLvl1' => $operator));

        return $this->render('AppBundle:Page/Operator:operator_show.html.twig', array(
            'operator'     => $operator,
            'operatorsformations' => $operatorsFormations,
            'supervisedFormations' => $supervisedFormations,
            'subordinates' => $subordinates,
            'roles' => $all_roles
        ));
    }

    public function showAllAction(Request $request)
    {	//Récupère tous les opérateurs et render la vue correspondante à la liste
        $em = $this->getDoctrine()->getRepository('AppBundle:User');

        $operators = $em->findAll();

		foreach($operators as $operator) {
			if($operator->getStatus() == 1) {
				$operator->setStatus(3);
				$em->persist($operator);
			}
		}
		$em->flush();


        return $this->render('AppBundle:Page/Operator:operator_show_all.html.twig', array(
            'operators'      => $operators,
        ));

    }

    public function deleteAction($idOp)
    { //Delete un opérateur
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

		/*Un peu barbare comme méthode j'imagine, mais j'ai pas trouvé comment faire autrement.
		 *On vérifie le rôle actuel (pour faire tous les cas) et on le remove pour mettre le nouveau
		*/

        if($operator->hasRole("ROLE_QUATHODE")) {
			$operator->removeRole("ROLE_QUATHODE");
			$operator->addRole("ROLE_RESPONSABLE");
		}
		else if($operator->hasRole("ROLE_RESPONSABLE")) {
			$operator->removeRole("ROLE_RESPONSABLE");
			$operator->addRole("ROLE_ADMIN");
		}
		else if($operator->hasRole("ROLE_TUTEUR")) {
			$operator->removeRole("ROLE_TUTEUR");
			$operator->addRole("ROLE_QUATHODE");
		}
		else if($operator->hasRole("ROLE_USER")) {
			$operator->removeRole("ROLE_USER");
			$operator->addRole("ROLE_TUTEUR");
		}

        $em->persist($operator);
        $em->flush();

        return $this->redirectToRoute('AppBundle_operator_show', array('idOp' => $operator->getId()));
    }

    public function unsetAdminAction($idOp)
    {
        $em = $this->getDoctrine()->getManager();

        $operator = $em->getRepository('AppBundle:User')->find($idOp);

		/*Un peu barbare comme méthode j'imagine, mais j'ai pas trouvé comment faire autrement.
		 *On vérifie le rôle actuel (pour faire tous les cas) et on le supprime pour mettre le nouveau
		*/

		if($operator->hasRole("ROLE_QUATHODE")) {
			$operator->removeRole("ROLE_QUATHODE");
			$operator->addRole("ROLE_TUTEUR");
		}
		else if($operator->hasRole("ROLE_ADMIN")) {
			$operator->removeRole("ROLE_ADMIN");
			$operator->addRole("ROLE_RESPONSABLE");
		}
		else if($operator->hasRole("ROLE_RESPONSABLE")) {
			$operator->removeRole("ROLE_RESPONSABLE");
			$operator->addRole("ROLE_QUATHODE");
		}
		else if($operator->hasRole("ROLE_TUTEUR")) {
			$operator->removeRole("ROLE_TUTEUR");
			$operator->addRole("ROLE_USER");
		}

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

	//Premier set du MDP à changer ici
    public function addAction(Request $request)
    {
        $operator = new User();

        $form = $this->createForm(OperatorType::class, $operator);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $operator = $form->getData();
            $operator->setUsername($operator->getRegistrationNumber());
            //$random = random_bytes(10);		//Premier set du MDP à changer ici
            $operator->setPlainPassword($operator->getRegistrationNumber());	//Le pwd est matricule
			$operator->addRole("ROLE_USER");
            $em = $this->getDoctrine()->getManager();

            $operator->setEnabled(true);	//Active le compte à la création (sinon on peut pas se connecter avec)
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
		$operator->setPlainPassword($operator->getRegistrationNumber());
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

			if (($handle = fopen("/var/salariesTXT_4.prn", "r")) !== FALSE) {
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
						/*for ($x=0; $x < count($mots); $x++) {
							echo $x . " - " . $mots[$x] . "<br />\n";
						}*/

						$nomNPlus1 = $em->getRepository('AppBundle:User')
							->findOneBy(array('lastName' => $mots[6]));
						$nomNPlus2 = $em->getRepository('AppBundle:User')
							->findOneBy(array('lastName' => "root"));

						//var_dump($nomNPlus1);

						//test
						if(!$nomNPlus1)
							echo "Nop no N+1 : ".$mots[6]."<br />";

						//parser le string en date
						//$dateEntree = date_parse($mots[5]);
						//print_r($dateEntree);

						//$mots5 = str_replace('/', '-', $mots[5]);
						//$date = DateTime::createFromFormat('j/m/Y', $mots[5]);
						//var_dump($date);

						//echo "<br /> coucou - ". strval($mots[0]) . "<br />";

						switch($mots[4]) {	//J'ai peut-être inversé les valeurs correspondantes
							case "CDI":
								$mots[4] = 1;
							case "CDD":
								$mots[4] = 2;
							case "int":
								$mots[4] = 3;
							default:
								$mots[4] = 1;
						}

						$operator->setRegistrationNumber(strval($mots[0]))
							->setLastName($mots[1])
							->setFirstName($mots[2])
							->setSite($mots[3])
							->setStatus($mots[4])
							//->setDateEntree($date)
							->setSuperiorLvl1($nomNPlus1)
							->setSuperiorLvl2($nomNPlus2);
						$operator->setUsername($operator->getRegistrationNumber());

						$operator->setPlainPassword($operator->getRegistrationNumber());


						$em = $this->getDoctrine()->getManager();
						$em->persist($operator);

				    }
				}
				//on flsuh en dehors de la boucle car coûteux
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
