<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminAlarma
 *
 * @ORM\Table(name="admin_alarma", indexes={@ORM\Index(name="IDX_4D69BE95D8285FD0", columns={"tipo_alarma_id"}), @ORM\Index(name="IDX_4D69BE95DB38439E", columns={"usuario_id"})})
 * @ORM\Entity
 */
class AdminAlarma
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_alarma_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="text", nullable=true)
     */
    private $descripcion;

    /**
     * @var integer
     *
     * @ORM\Column(name="entidad_id", type="integer", nullable=true)
     */
    private $entidadId;

    /**
     * @var boolean
     *
     * @ORM\Column(name="leido", type="boolean", nullable=true)
     */
    private $leido;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_creacion", type="datetime", nullable=true)
     */
    private $fechaCreacion;

    /**
     * @var \Link\ComunBundle\Entity\AdminTipoAlarma
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\AdminTipoAlarma")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_alarma_id", referencedColumnName="id")
     * })
     */
    private $tipoAlarma;

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
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return AdminAlarma
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    
        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set entidadId
     *
     * @param integer $entidadId
     *
     * @return AdminAlarma
     */
    public function setEntidadId($entidadId)
    {
        $this->entidadId = $entidadId;
    
        return $this;
    }

    /**
     * Get entidadId
     *
     * @return integer
     */
    public function getEntidadId()
    {
        return $this->entidadId;
    }

    /**
     * Set leido
     *
     * @param boolean $leido
     *
     * @return AdminAlarma
     */
    public function setLeido($leido)
    {
        $this->leido = $leido;
    
        return $this;
    }

    /**
     * Get leido
     *
     * @return boolean
     */
    public function getLeido()
    {
        return $this->leido;
    }

    /**
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     *
     * @return AdminAlarma
     */
    public function setFechaCreacion($fechaCreacion)
    {
        $this->fechaCreacion = $fechaCreacion;
    
        return $this;
    }

    /**
     * Get fechaCreacion
     *
     * @return \DateTime
     */
    public function getFechaCreacion()
    {
        return $this->fechaCreacion;
    }

    /**
     * Set tipoAlarma
     *
     * @param \Link\ComunBundle\Entity\AdminTipoAlarma $tipoAlarma
     *
     * @return AdminAlarma
     */
    public function setTipoAlarma(\Link\ComunBundle\Entity\AdminTipoAlarma $tipoAlarma = null)
    {
        $this->tipoAlarma = $tipoAlarma;
    
        return $this;
    }

    /**
     * Get tipoAlarma
     *
     * @return \Link\ComunBundle\Entity\AdminTipoAlarma
     */
    public function getTipoAlarma()
    {
        return $this->tipoAlarma;
    }

    /**
     * Set usuario
     *
     * @param \Link\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return AdminAlarma
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
}
