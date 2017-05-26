<?php

namespace AppBundle\Controller;

use AppBundle\Entity\OperatorFormation;
use AppBundle\Entity\OperatorCategory;
use AppBundle\Form\Type\OperatorFormationType;
use HTML2PDF;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class OperatorFormationExportController extends Controller
{

    public function exportToPDFAction($idOpForm, Request $request)
    {	//https://numa-bord.com/miniblog/symfony-generer-pdf-enseparhtml2pdfbundle/
		//http://stackoverflow.com/questions/13947328/how-to-add-script-tag-in-html2pdf

		$em = $this->getDoctrine()->getManager();

        $operatorformation = $em->getRepository('AppBundle:OperatorFormation')->find($idOpForm);

        if (!$operatorformation) {
            throw $this->createNotFoundException('Pas d\'objet');
        }


		//on stocke la vue à convertir en PDF, en n'oubliant pas les paramètres twig si la vue comporte des données dynamiques
		$html = $this->renderView('AppBundle:Page/OperatorFormation:operatorformation_show_export.html.twig', array(
            'operatorformation'     => $operatorformation,
        ));

        //$html2pdf = new HTML2PDF('P', 'A4', 'fr', false, 'ISO-8859-1', array(6, 6, 6, 0));
		//on appelle le service html2pdf
		$html2pdf = $this->get('html2pdf_factory')->create();
		//real : utilise la taille réelle
		$html2pdf->pdf->SetDisplayMode('real');

		//writeHTML va tout simplement prendre la vue stockée dans la variable $html pour la convertir en format PDF
		$html2pdf->writeHTML($html);


		//Output envoit le document PDF au navigateur internet
	return new Response($html2pdf->Output($operatorformation.'-FFO.pdf', 'D'), 200, array('Content-Type' => 'application/pdf'));
    }

}

