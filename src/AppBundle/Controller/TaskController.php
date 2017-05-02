<?php
namespace AppBundle\Controller;


use AppBundle\Entity\Task;
use AppBundle\Form\Type\TaskType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TaskController extends Controller
{

    private function changeOrdre($idTask, $delta_ordre) {
        $em = $this->getDoctrine()->getManager();
        $task = $em->getRepository('AppBundle:Task')->find($idTask);
        $ordre = $task->getOrdre();
        $category = $task->getCategory();
        $tasks = $category->getTasks();

        $task_tmp = null;
        foreach($tasks as $task_for_tmp) {
            if($task_for_tmp->getOrdre() == $ordre + $delta_ordre)
                $task_tmp = $task_for_tmp;
        }

        if($task_tmp !== null ) {
            $task_tmp->setOrdre($ordre);
            $task->setOrdre($ordre + $delta_ordre);

            $em->persist($task);
            $em->persist($task_for_tmp);
            $em->flush();
        }

        return $this->redirectToRoute('AppBundle_formation_show', array('idForm' => $category->getFormation()->getId()));
    }

    public function upAction($idTask){
        return $this->changeOrdre($idTask, -1);
    }

    public function downAction($idTask){
        return $this->changeOrdre($idTask, +1);
    }


    public function addAction(Request $request, $idCat)
    {
        $task = new Task();
        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository('AppBundle:Category')->find($idCat);
        $task->setCategory($category);
        $task->setOrdre(sizeof($category->getTasks()));

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();

            if ($form->get('saveAndAdd')->isClicked()) {
                return $this->redirectToRoute('AppBundle_task_add',
                    ['idCat' => $task->getCategory()->getId()]
                );
            } else {
                return $this->redirectToRoute('AppBundle_formation_show',
                    ['idForm' => $task->getCategory()->getFormation()->getId()]
                );
            }
        }

        return $this->render('AppBundle:Page/Task:task_add.html.twig', array(
            'form' => $form->createView(),
        ));
    }


    public function deleteAction(Request $request, $idTask)
    {
        $em = $this->getDoctrine()->getManager();
        $task = $em->getRepository('AppBundle:Task')->find($idTask);

        if (!$task) {
            throw $this->createNotFoundException('Pas d\'objet');
        }

        $em->remove($task);
        $em->flush();

        return $this->redirectToRoute('AppBundle_formation_show',
            ['idForm' => $task->getCategory()->getFormation()->getId()]
        );
    }


    public function editAction(Request $request, $idTask)
    {
        $em = $this->getDoctrine()->getManager();
        $task = $em->getRepository('AppBundle:Task')->find($idTask);
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();

            return $this->redirectToRoute('AppBundle_formation_show', array('idForm' => $task->getCategory()->getFormation()->getId()));
        }

        return $this->render('AppBundle:Page/Task:task_edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
