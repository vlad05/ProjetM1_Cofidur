<?php
namespace AppBundle\Controller;


use AppBundle\Entity\OperatorTask;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class OperatorTaskController extends Controller
{

    public function addAction($idOpForm, $idCategory, $idTask)
    {
        $em = $this->getDoctrine()->getManager();

       	$operatorformation = $em->getRepository('AppBundle:OperatorFormation')->find($idOpForm);
        $category = $em->getRepository('AppBundle:Category')->find($idCategory);
        $task = $em->getRepository('AppBundle:Task')->find($idTask);
        $operatorcategory = $em->getRepository('AppBundle:OperatorCategory')
                                ->findBy(['operatorformation'=>$operatorformation, 'category'=>$category]);

        $operatortask = new OperatorTask();
        $operatortask->setOperatorcategory($operatorcategory[0]);
        $operatortask->setTask($task);
        $operatortask->setValidation(true);

        $em->persist($operatortask);
        $em->flush();

        return $this->redirectToRoute('AppBundle_operatorformation_show', array('idOpForm' => $idOpForm));
    }

    public function deleteAction($idOpForm, $idCategory, $idTask)
    {
        $em = $this->getDoctrine()->getManager();

        $operatorformation = $em->getRepository('AppBundle:OperatorFormation')->find($idOpForm);
        $category = $em->getRepository('AppBundle:Category')->find($idCategory);
        $task = $em->getRepository('AppBundle:Task')->find($idTask);
        $operatorcategory = $em->getRepository('AppBundle:OperatorCategory')
                                ->findBy(array('operatorformation'=>$operatorformation, 'category'=>$category));

        $operatortasks = $em->getRepository('AppBundle:OperatorTask')
                            ->findBy(['operatorcategory'=>$operatorcategory, 'task'=>$task]);

        foreach ($operatortasks as $operatortask) {
            $em->remove($operatortask);
        }

        $em->flush();

        return $this->redirectToRoute('AppBundle_operatorformation_show', array('idOpForm' => $idOpForm));
    }

}
