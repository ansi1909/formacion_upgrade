<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminNotificacion
 *
 * @ORM\Table(name="admin_notificacion", indexes={@ORM\Index(name="notificacion_ndx1", columns={"usuario_id"}), @ORM\Index(name="IDX_BDBBB019B394FE3", columns={"tipo_notificacion_id"}), @ORM\Index(name="IDX_BDBBB01521E1991", columns={"empresa_id"})})
 * @ORM\Entity
 */
class AdminNotificacion
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_notificacion_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="asunto", type="string", length=500, nullable=true)
     */
    private $asunto;

    /**
     * @var string
     *
     * @ORM\Column(name="mensaje", type="text", nullable=true)
     */
    private $mensaje;

    /**
     * @var \Link\ComunBundle\Entity\AdminTipoNotificacion
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\AdminTipoNotificacion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_notificacion_id", referencedColumnName="id")
     * })
     */
    private $tipoNotificacion;

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
     * @var \Link\ComunBundle\Entity\AdminEmpresa
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\AdminEmpresa")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="empresa_id", referencedColumnName="id")
     * })
     */
    private $empresa;



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
     * Set asunto
     *
     * @param string $asunto
     *
     * @return AdminNotificacion
     */
    public function setAsunto($asunto)
    {
        $this->asunto = $asunto;
    
        return $this;
    }

    /**
     * Get asunto
     *
     * @return string
     */
    public function getAsunto()
    {
        return $this->asunto;
    }

    /**
     * Set mensaje
     *
     * @param string $mensaje
     *
     * @return AdminNotificacion
     */
    public function setMensaje($mensaje)
    {
        $this->mensaje = $mensaje;
    
        return $this;
    }

    /**
     * Get mensaje
     *
     * @return string
     */
    public function getMensaje()
    {
        return $this->mensaje;
    }

    /**
     * Set tipoNotificacion
     *
     * @param \Link\ComunBundle\Entity\AdminTipoNotificacion $tipoNotificacion
     *
     * @return AdminNotificacion
     */
    public function setTipoNotificacion(\Link\ComunBundle\Entity\AdminTipoNotificacion $tipoNotificacion = null)
    {
        $this->tipoNotificacion = $tipoNotificacion;
    
        return $this;
    }

    /**
     * Get tipoNotificacion
     *
     * @return \Link\ComunBundle\Entity\AdminTipoNotificacion
     */
    public function getTipoNotificacion()
    {
        return $this->tipoNotificacion;
    }

    /**
     * Set usuario
     *
     * @param \Link\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return AdminNotificacion
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
     * Set empresa
     *
     * @param \Link\ComunBundle\Entity\AdminEmpresa $empresa
     *
     * @return AdminNotificacion
     */
    public function setEmpresa(\Link\ComunBundle\Entity\AdminEmpresa $empresa = null)
    {
        $this->empresa = $empresa;
    
        return $this;
    }

    /**
     * Get empresa
     *
     * @return \Link\ComunBundle\Entity\AdminEmpresa
     */
    public function getEmpresa()
    {
        return $this->empresa;
    }
    /**
     * @var \DateTime
     */
    private $fecha;


    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     *
     * @return AdminNotificacion
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
}
