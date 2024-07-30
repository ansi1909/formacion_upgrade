<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminCorreo
 *
 * @ORM\Table(name="admin_correo", indexes={@ORM\Index(name="IDX_2ACC3A30FEC8CA14", columns={"tipo_correo_id"}), @ORM\Index(name="IDX_2ACC3A30DB38439E", columns={"usuario_id"})})
 * @ORM\Entity
 */
class AdminCorreo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_correo_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="entidad_id", type="integer", nullable=true)
     */
    private $entidadId;

    /**
     * @var string
     *
     * @ORM\Column(name="correo", type="string", length=100, nullable=true)
     */
    private $correo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="datetime", nullable=true)
     */
    private $fecha;

    /**
     * @var \Link\ComunBundle\Entity\AdminTipoCorreo
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\AdminTipoCorreo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_correo_id", referencedColumnName="id")
     * })
     */
    private $tipoCorreo;

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
     * Set entidadId
     *
     * @param integer $entidadId
     *
     * @return AdminCorreo
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
     * Set correo
     *
     * @param string $correo
     *
     * @return AdminCorreo
     */
    public function setCorreo($correo)
    {
        $this->correo = $correo;
    
        return $this;
    }

    /**
     * Get correo
     *
     * @return string
     */
    public function getCorreo()
    {
        return $this->correo;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     *
     * @return AdminCorreo
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    
        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set tipoCorreo
     *
     * @param \Link\ComunBundle\Entity\AdminTipoCorreo $tipoCorreo
     *
     * @return AdminCorreo
     */
    public function setTipoCorreo(\Link\ComunBundle\Entity\AdminTipoCorreo $tipoCorreo = null)
    {
        $this->tipoCorreo = $tipoCorreo;
    
        return $this;
    }

    /**
     * Get tipoCorreo
     *
     * @return \Link\ComunBundle\Entity\AdminTipoCorreo
     */
    public function getTipoCorreo()
    {
        return $this->tipoCorreo;
    }

    /**
     * Set usuario
     *
     * @param \Link\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return AdminCorreo
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
