<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminLayout
 *
 * @ORM\Table(name="admin_layout")
 * @ORM\Entity
 */
class AdminLayout
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_layout_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="twig", type="string", length=100, nullable=true)
     */
    private $twig;



    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set twig
     *
     * @param string $twig
     *
     * @return AdminLayout
     */
    public function setTwig($twig)
    {
        $this->twig = $twig;

        return $this;
    }

    /**
     * Get twig
     *
     * @return string
     */
    public function getTwig()
    {
        return $this->twig;
    }
}
