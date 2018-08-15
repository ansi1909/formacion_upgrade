<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminRol
 *
 * @ORM\Table(name="admin_rol")
 * @ORM\Entity
 */
class AdminRol
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_rol_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=50, nullable=true)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="text", nullable=true)
     */
    private $descripcion;

    /**
     * @var boolean
     *
     * @ORM\Column(name="empresa", type="boolean", nullable=true)
     */
    private $empresa;

    /**
     * @var boolean
     *
     * @ORM\Column(name="backend", type="boolean", nullable=true)
     */
    private $backend;



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
     * Set nombre
     *
     * @param string $nombre
     *
     * @return AdminRol
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    
        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return AdminRol
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
     * Set empresa
     *
     * @param boolean $empresa
     *
     * @return AdminRol
     */
    public function setEmpresa($empresa)
    {
        $this->empresa = $empresa;
    
        return $this;
    }

    /**
     * Get empresa
     *
     * @return boolean
     */
    public function getEmpresa()
    {
        return $this->empresa;
    }

    /**
     * Set backend
     *
     * @param boolean $backend
     *
     * @return AdminRol
     */
    public function setBackend($backend)
    {
        $this->backend = $backend;
    
        return $this;
    }

    /**
     * Get backend
     *
     * @return boolean
     */
    public function getBackend()
    {
        return $this->backend;
    }
}
