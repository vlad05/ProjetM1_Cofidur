<?php 
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class ImportSalaries
{
    // ...

    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank(message="Veuillez selectionner un fichier .prn SVP.")
     * @Assert\File(mimeTypes={ "application/prn" })
     */
    private $importSalaries;

    public function getImportSalaries()
    {
        return $this->importSalaries;
    }

    public function setImportSalaries($importSalaries)
    {
        $this->importSalaries = $importSalaries;

        return $this;
    }
}
