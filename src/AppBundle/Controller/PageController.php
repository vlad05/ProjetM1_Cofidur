<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Swift_Message;
use \Swift_SmtpTransport;

class PageController extends Controller
{
    public function indexAction()
    {
        return $this->render('AppBundle:Page:index.html.twig');
    }

    public function aboutAction()
    {
        return $this->render('AppBundle:Page:about.html.twig');
    }

    public function contactAction()
    {
        return $this->render('AppBundle:Page:contact.html.twig');
    }

    public function adminAction()
    {
        return $this->render('AppBundle:Page:admin.html.twig');
    }

    public function profileAction()
    {
		return $this->render('FOSUserBundle:views:User:user_profile.html.twig');
	}

	public function notesVersionAction()
	{
		return $this->render('AppBundle:Page:notesVersions.html.twig');
	}


	/* Sert à l'envoi des alarmes (par mail) des FO qui vont bientôt se terminer */
	public function testMailAction()
	{
		//à supprimer
		/*$transport = (new Swift_SmtpTransport('94.130.2.200', 25));
		$transport->setUsername('lvlpgran');
		$transport->setPassword('pierregr');

		//$mailer = new \Swift_Mailer($transport);*/
		$em = $this->getDoctrine()->getRepository('AppBundle:OperatorFormation');

        $operatorsformations = $em->findAll();

		/*$message = \Swift_Message::newInstance();
		$message->setSubject('Test mail');
		$message->setFrom('piergranier77@gmail.com');
		$message->setTo('piergranier77@gmail.com');
		//On passe la vue twig dans le body du message et hop ça fait des chocapics
		$message->setBody($this->renderView('AppBundle:Page/OperatorFormation:operatorformation_mail.html.twig', array(
            'operatorsformations'      => $operatorsformations,
        ));

        $this->get('mailer')->send($message);
		*/
        return $this->render('AppBundle:Page/OperatorFormation:operatorformation_mail.html.twig', array(
            'operatorsformations'      => $operatorsformations,
        ));
	}
}
