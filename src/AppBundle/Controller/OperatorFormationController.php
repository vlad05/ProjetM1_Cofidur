<?php

namespace AppBundle\Controller;

use AppBundle\Entity\OperatorFormation;
use AppBundle\Entity\OperatorCategory;

use AppBundle\Form\Type\OperatorFormationType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class OperatorFormationController extends Controller
{

    public function addAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $operatorformation = new OperatorFormation();

        $form = $this->createForm(OperatorFormationType::class, $operatorformation);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $operatorformation = $form->getData();

            $em->persist($operatorformation);

            $formation = $em->getRepository('AppBundle:Formation')->find($operatorformation->getFormation());
            $categories = $formation->getCategories();
            foreach ($categories as $cat) {
                $operatorcategory = new OperatorCategory();
                $operatorcategory->setCategory($cat);
                $operatorcategory->setOperatorformation($operatorformation);
                $em->persist($operatorcategory);
            }

            $em->flush();

            return $this->redirectToRoute('AppBundle_operatorformation_show_all');
        }

        return $this->render('AppBundle:Page/OperatorFormation:operatorformation_add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function showAction($idOpForm)
    {
        $em = $this->getDoctrine()->getManager();

        $operatorformation = $em->getRepository('AppBundle:OperatorFormation')->find($idOpForm);

        if (!$operatorformation) {
            throw $this->createNotFoundException('Pas d\'objet');
        }

        return $this->render('AppBundle:Page/OperatorFormation:operatorformation_show.html.twig', array(
            'operatorformation'     => $operatorformation,
        ));
    }

    public function showAllAction()
    {
        $em = $this->getDoctrine()->getRepository('AppBundle:OperatorFormation');

        $operatorsformations = $em->findAll();

        return $this->render('AppBundle:Page/OperatorFormation:operatorformation_show_all.html.twig', array(
            'operatorsformations'      => $operatorsformations,
        ));
    }

    public function deleteAction($idOpForm)
    {
        $em = $this->getDoctrine()->getManager();

        $operatorformation = $em->getRepository('AppBundle:OperatorFormation')->find($idOpForm);

        if (!$operatorformation) {
            throw $this->createNotFoundException('Pas d\'objet');
        }

        $em->remove($operatorformation);
        $em->flush();

        return $this->redirectToRoute('AppBundle_operatorformation_show_all');
    }

    public function editAction(Request $request, $idOpForm)
    {
        $em = $this->getDoctrine()->getManager();

        $operatorformation = $em->getRepository('AppBundle:OperatorFormation')->find($idOpForm);

        if (!$operatorformation) {
            throw $this->createNotFoundException('Pas d\'objet');
        }

        $form = $this->createForm(OperatorFormationType::class, $operatorformation);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $operatorformation = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($operatorformation);
            $em->flush();

            return $this->redirectToRoute('AppBundle_operatorformation_show_all');
        }

        return $this->render('AppBundle:Page/OperatorFormation:operatorformation_add.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
