<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminNotificacion
 *
 * @ORM\Table(name="admin_notificacion", indexes={@ORM\Index(name="notificacion_ndx1", columns={"usuario_id", "usuario_tutor_id"}), @ORM\Index(name="IDX_BDBBB019B394FE3", columns={"tipo_notificacion_id"}), @ORM\Index(name="IDX_BDBBB01DB38439E", columns={"usuario_id"}), @ORM\Index(name="IDX_BDBBB01C84E9668", columns={"usuario_tutor_id"})})
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
     * @var integer
     *
     * @ORM\Column(name="valor_notificacion", type="integer", nullable=true)
     */
    private $valorNotificacion;

    /**
     * @var boolean
     *
     * @ORM\Column(name="leido", type="boolean", nullable=true)
     */
    private $leido;

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
     * @var \Link\ComunBundle\Entity\AdminUsuario
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\AdminUsuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="usuario_tutor_id", referencedColumnName="id")
     * })
     */
    private $usuarioTutor;



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
     * Set valorNotificacion
     *
     * @param integer $valorNotificacion
     *
     * @return AdminNotificacion
     */
    public function setValorNotificacion($valorNotificacion)
    {
        $this->valorNotificacion = $valorNotificacion;

        return $this;
    }

    /**
     * Get valorNotificacion
     *
     * @return integer
     */
    public function getValorNotificacion()
    {
        return $this->valorNotificacion;
    }

    /**
     * Set leido
     *
     * @param boolean $leido
     *
     * @return AdminNotificacion
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
     * Set usuarioTutor
     *
     * @param \Link\ComunBundle\Entity\AdminUsuario $usuarioTutor
     *
     * @return AdminNotificacion
     */
    public function setUsuarioTutor(\Link\ComunBundle\Entity\AdminUsuario $usuarioTutor = null)
    {
        $this->usuarioTutor = $usuarioTutor;

        return $this;
    }

    /**
     * Get usuarioTutor
     *
     * @return \Link\ComunBundle\Entity\AdminUsuario
     */
    public function getUsuarioTutor()
    {
        return $this->usuarioTutor;
    }
}
