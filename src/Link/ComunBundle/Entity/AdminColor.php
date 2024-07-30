<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminColor
 *
 * @ORM\Table(name="admin_color", indexes={@ORM\Index(name="IDX_C67CB313432D1DA2", columns={"preferencia_id"}), @ORM\Index(name="IDX_C67CB313A55FF1F3", columns={"atributo_id"})})
 * @ORM\Entity
 */
class AdminColor
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_color_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="hex", type="string", length=10, nullable=true)
     */
    private $hex;

    /**
     * @var \Link\ComunBundle\Entity\AdminPreferencia
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\AdminPreferencia")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="preferencia_id", referencedColumnName="id")
     * })
     */
    private $preferencia;

    /**
     * @var \Link\ComunBundle\Entity\AdminAtributo
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\AdminAtributo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="atributo_id", referencedColumnName="id")
     * })
     */
    private $atributo;



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
     * Set hex
     *
     * @param string $hex
     *
     * @return AdminColor
     */
    public function setHex($hex)
    {
        $this->hex = $hex;
    
        return $this;
    }

    /**
     * Get hex
     *
     * @return string
     */
    public function getHex()
    {
        return $this->hex;
    }

    /**
     * Set preferencia
     *
     * @param \Link\ComunBundle\Entity\AdminPreferencia $preferencia
     *
     * @return AdminColor
     */
    public function setPreferencia(\Link\ComunBundle\Entity\AdminPreferencia $preferencia = null)
    {
        $this->preferencia = $preferencia;
    
        return $this;
    }

    /**
     * Get preferencia
     *
     * @return \Link\ComunBundle\Entity\AdminPreferencia
     */
    public function getPreferencia()
    {
        return $this->preferencia;
    }

    /**
     * Set atributo
     *
     * @param \Link\ComunBundle\Entity\AdminAtributo $atributo
     *
     * @return AdminColor
     */
    public function setAtributo(\Link\ComunBundle\Entity\AdminAtributo $atributo = null)
    {
        $this->atributo = $atributo;
    
        return $this;
    }

    /**
     * Get atributo
     *
     * @return \Link\ComunBundle\Entity\AdminAtributo
     */
    public function getAtributo()
    {
        return $this->atributo;
    }
}
