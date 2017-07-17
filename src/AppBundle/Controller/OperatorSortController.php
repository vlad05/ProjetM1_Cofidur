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

class OperatorSortController extends Controller
{

	/*public function operatorAction(Request $request)
	{

	}*/

    public function showAction($idOp)
    {
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
    {
       /* $em = $this->getDoctrine()->getRepository('AppBundle:User');

        $operators = $em->findAll();

        return $this->render('AppBundle:Page/Operator:operator_show_all.html.twig', array(
            'operators'      => $operators,
        ));*/
       	$em = $this->getDoctrine()->getManager();

        $form = $this->createForm(OperatorSortType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //traitement spécifique au formulaire
            $allConnexions= [];

            $operatorTabRecherche = [];


            //Nom et prénom
            if($form["lastName"]->getData() != null){
                $operatorTabRecherche["lastName"] = $form["lastName"]->getData()->getLastName();
            }


            //si les tab non vide
            if(count($operatorTabRecherche) != 0){
                $operators = $em->getRepository('AppBundle:User')->findBy($operatorTabRecherche);
            }else{
                $operators= $em->getRepository('AppBundle:User')->findAll();
            }
		}else { //$form invalide
			$operators= $em->getRepository('AppBundle:User')->findAll();
		}
        return $this->render('AppBundle:Page/Operator:operator_show_all.html.twig', array(
            'form'                         => $form->createView(),
            'operators'                    => $operators,
        ));
    }






}
