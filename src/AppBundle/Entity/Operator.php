<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Operator
 *
 * @ORM\Table(name="operator")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OperatorRepository")
 */
class Operator extends User
{

}
