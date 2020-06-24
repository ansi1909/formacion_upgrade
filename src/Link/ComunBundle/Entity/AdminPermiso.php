<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminPermiso
 *
 * @ORM\Table(name="admin_permiso", indexes={@ORM\Index(name="IDX_394629173AF686C8", columns={"aplicacion_id"}), @ORM\Index(name="IDX_394629174BAB96C", columns={"rol_id"})})
 * @ORM\Entity
 */
class AdminPermiso
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_permiso_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \Link\ComunBundle\Entity\AdminAplicacion
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\AdminAplicacion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="aplicacion_id", referencedColumnName="id")
     * })
     */
    private $aplicacion;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set aplicacion
     *
     * @param \Link\ComunBundle\Entity\AdminAplicacion $aplicacion
     *
     * @return AdminPermiso
     */
    public function setAplicacion(\Link\ComunBundle\Entity\AdminAplicacion $aplicacion = null)
    {
        $this->aplicacion = $aplicacion;
    
        return $this;
    }

    /**
     * Get aplicacion
     *
     * @return \Link\ComunBundle\Entity\AdminAplicacion
     */
    public function getAplicacion()
    {
        return $this->aplicacion;
    }

    /**
     * Set rol
     *
     * @param \Link\ComunBundle\Entity\AdminRol $rol
     *
     * @return AdminPermiso
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
}
