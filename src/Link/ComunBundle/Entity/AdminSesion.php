<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminSesion
 *
 * @ORM\Table(name="admin_sesion", indexes={@ORM\Index(name="sesion_ndx1", columns={"usuario_id"})})
 * @ORM\Entity
 */
class AdminSesion
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_sesion_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_ingreso", type="datetime", nullable=true)
     */
    private $fechaIngreso;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_request", type="datetime", nullable=true)
     */
    private $fechaRequest;

    /**
     * @var boolean
     *
     * @ORM\Column(name="disponible", type="boolean", nullable=true)
     */
    private $disponible;

    /**
     * @var \Link\ComunBundle\Entity\AdminUsuario
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\AdminUsuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     * })
     */
    private $usuario;



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
     * Set fechaIngreso
     *
     * @param \DateTime $fechaIngreso
     *
     * @return AdminSesion
     */
    public function setFechaIngreso($fechaIngreso)
    {
        $this->fechaIngreso = $fechaIngreso;
    
        return $this;
    }

    /**
     * Get fechaIngreso
     *
     * @return \DateTime
     */
    public function getFechaIngreso()
    {
        return $this->fechaIngreso;
    }

    /**
     * Set fechaRequest
     *
     * @param \DateTime $fechaRequest
     *
     * @return AdminSesion
     */
    public function setFechaRequest($fechaRequest)
    {
        $this->fechaRequest = $fechaRequest;
    
        return $this;
    }

    /**
     * Get fechaRequest
     *
     * @return \DateTime
     */
    public function getFechaRequest()
    {
        return $this->fechaRequest;
    }

    /**
     * Set disponible
     *
     * @param boolean $disponible
     *
     * @return AdminSesion
     */
    public function setDisponible($disponible)
    {
        $this->disponible = $disponible;
    
        return $this;
    }

    /**
     * Get disponible
     *
     * @return boolean
     */
    public function getDisponible()
    {
        return $this->disponible;
    }

    /**
     * Set usuario
     *
     * @param \Link\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return AdminSesion
     */
    public function setUsuario(\Link\ComunBundle\Entity\AdminUsuario $usuario = null)
    {
        $this->usuario = $usuario;
    
        return $this;
    }

    /**
     * Get usuario
     *
     * @return \Link\ComunBundle\Entity\AdminUsuario
     */
    public function getUsuario()
    {
        return $this->usuario;
    }
    /**
     * @var string
     */
    private $dispositivo;


    /**
     * Set dispositivo
     *
     * @param string $dispositivo
     *
     * @return AdminSesion
     */
    public function setDispositivo($dispositivo)
    {
        $this->dispositivo = $dispositivo;
    
        return $this;
    }

    /**
     * Get dispositivo
     *
     * @return string
     */
    public function getDispositivo()
    {
        return $this->dispositivo;
    }
}
