<?php
namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\Type\OperatorType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
}
