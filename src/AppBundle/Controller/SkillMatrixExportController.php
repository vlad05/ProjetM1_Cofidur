<?php
namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;



class SkillMatrixExportController extends Controller
{

    public function skillMatrixExportAction(Request $request)
    {
		// ask the service for a excel object
		$phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

		$phpExcelObject->getProperties()->setCreator("liuggio")	//à changer
		   ->setLastModifiedBy("Giulio De Donato")				// à changer
		   ->setTitle("Matrice des FFO")
		   ->setDescription("Document generated using PHP classes.")
		   ->setKeywords("office 2005 openxml php")
		   ->setCategory("Test result file");
		$phpExcelObject->setActiveSheetIndex(0)
		   ->setCellValue('A1', 'Hello')
		   ->setCellValue('B2', 'world!');
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
			'PhpExcelFileSample.xlsx'
		);
		$response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
		$response->headers->set('Pragma', 'public');
		$response->headers->set('Cache-Control', 'maxage=1');
		$response->headers->set('Content-Disposition', $dispositionHeader);

		return $response;
    }

}
?>
