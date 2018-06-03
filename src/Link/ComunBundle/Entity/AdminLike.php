<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminLike
 *
 * @ORM\Table(name="admin_like", indexes={@ORM\Index(name="IDX_8C7AC253FFEB5B27", columns={"social_id"}), @ORM\Index(name="IDX_8C7AC253DB38439E", columns={"usuario_id"})})
 * @ORM\Entity
 */
class AdminLike
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_like_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="entidad_id", type="integer", nullable=true)
     */
    private $entidadId;

    /**
     * @var \Link\ComunBundle\Entity\AdminSocial
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\AdminSocial")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="social_id", referencedColumnName="id")
     * })
     */
    private $social;

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
     * @return AdminLike
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
     * Set social
     *
     * @param \Link\ComunBundle\Entity\AdminSocial $social
     *
     * @return AdminLike
     */
    public function setSocial(\Link\ComunBundle\Entity\AdminSocial $social = null)
    {
        $this->social = $social;
    
        return $this;
    }

    /**
     * Get social
     *
     * @return \Link\ComunBundle\Entity\AdminSocial
     */
    public function getSocial()
    {
        return $this->social;
    }

    /**
     * Set usuario
     *
     * @param \Link\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return AdminLike
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
