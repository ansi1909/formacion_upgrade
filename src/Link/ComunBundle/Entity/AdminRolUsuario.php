<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminRolUsuario
 *
 * @ORM\Table(name="admin_rol_usuario", indexes={@ORM\Index(name="rol_usuario_ndx1", columns={"rol_id", "usuario_id"}), @ORM\Index(name="IDX_F967B0D34BAB96C", columns={"rol_id"}), @ORM\Index(name="IDX_F967B0D3DB38439E", columns={"usuario_id"})})
 * @ORM\Entity
 */
class AdminRolUsuario
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_rol_usuario_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \Link\ComunBundle\Entity\AdminRol
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\AdminRol")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="rol_id", referencedColumnName="id")
     * })
     */
    private $rol;

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
     * Set rol
     *
     * @param \Link\ComunBundle\Entity\AdminRol $rol
     *
     * @return AdminRolUsuario
     */
    public function setRol(\Link\ComunBundle\Entity\AdminRol $rol = null)
    {
        $this->rol = $rol;

        return $this;
    }

    /**
     * Get rol
     *
     * @return \Link\ComunBundle\Entity\AdminRol
     */
    public function getRol()
    {
        return $this->rol;
    }

    /**
     * Set usuario
     *
     * @param \Link\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return AdminRolUsuario
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
