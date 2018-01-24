<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminNotificacionProgramada
 *
 * @ORM\Table(name="admin_notificacion_programada", indexes={@ORM\Index(name="IDX_CA62D9964D633FC4", columns={"notificacion_id"}), @ORM\Index(name="IDX_CA62D99675B0043D", columns={"tipo_destino_id"}), @ORM\Index(name="IDX_CA62D9969C833003", columns={"grupo_id"}), @ORM\Index(name="IDX_CA62D996DB38439E", columns={"usuario_id"})})
 * @ORM\Entity
 */
class AdminNotificacionProgramada
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_notificacion_programada_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="entidad_id", type="integer", nullable=true)
     */
    private $entidadId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_difusion", type="datetime", nullable=true)
     */
    private $fechaDifusion;

    /**
     * @var \Link\ComunBundle\Entity\AdminNotificacion
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\AdminNotificacion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="notificacion_id", referencedColumnName="id")
     * })
     */
    private $notificacion;

    /**
     * @var \Link\ComunBundle\Entity\AdminTipoDestino
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\AdminTipoDestino")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_destino_id", referencedColumnName="id")
     * })
     */
    private $tipoDestino;

    /**
     * @var \Link\ComunBundle\Entity\AdminNotificacionProgramada
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\AdminNotificacionProgramada")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grupo_id", referencedColumnName="id")
     * })
     */
    private $grupo;

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
     * @return AdminNotificacionProgramada
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
     * Set fechaDifusion
     *
     * @param \DateTime $fechaDifusion
     *
     * @return AdminNotificacionProgramada
     */
    public function setFechaDifusion($fechaDifusion)
    {
        $this->fechaDifusion = $fechaDifusion;
    
        return $this;
    }

    /**
     * Get fechaDifusion
     *
     * @return \DateTime
     */
    public function getFechaDifusion()
    {
        return $this->fechaDifusion;
    }

    /**
     * Set notificacion
     *
     * @param \Link\ComunBundle\Entity\AdminNotificacion $notificacion
     *
     * @return AdminNotificacionProgramada
     */
    public function setNotificacion(\Link\ComunBundle\Entity\AdminNotificacion $notificacion = null)
    {
        $this->notificacion = $notificacion;
    
        return $this;
    }

    /**
     * Get notificacion
     *
     * @return \Link\ComunBundle\Entity\AdminNotificacion
     */
    public function getNotificacion()
    {
        return $this->notificacion;
    }

    /**
     * Set tipoDestino
     *
     * @param \Link\ComunBundle\Entity\AdminTipoDestino $tipoDestino
     *
     * @return AdminNotificacionProgramada
     */
    public function setTipoDestino(\Link\ComunBundle\Entity\AdminTipoDestino $tipoDestino = null)
    {
        $this->tipoDestino = $tipoDestino;
    
        return $this;
    }

    /**
     * Get tipoDestino
     *
     * @return \Link\ComunBundle\Entity\AdminTipoDestino
     */
    public function getTipoDestino()
    {
        return $this->tipoDestino;
    }

    /**
     * Set grupo
     *
     * @param \Link\ComunBundle\Entity\AdminNotificacionProgramada $grupo
     *
     * @return AdminNotificacionProgramada
     */
    public function setGrupo(\Link\ComunBundle\Entity\AdminNotificacionProgramada $grupo = null)
    {
        $this->grupo = $grupo;
    
        return $this;
    }

    /**
     * Get grupo
     *
     * @return \Link\ComunBundle\Entity\AdminNotificacionProgramada
     */
    public function getGrupo()
    {
        return $this->grupo;
    }

    /**
     * Set usuario
     *
     * @param \Link\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return AdminNotificacionProgramada
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
