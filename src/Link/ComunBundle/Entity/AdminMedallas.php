<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminMedallas
 *
 * @ORM\Table(name="admin_medallas")
 * @ORM\Entity
 */
class AdminMedallas
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_medallas_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="nombre", type="string", length=100, nullable=true)
     */
    private $nombre;

    /**
     * @var string|null
     *
     * @ORM\Column(name="descripcion", type="string", length=100, nullable=true)
     */
    private $descripcion;

    /**
     * @var int|null
     *
     * @ORM\Column(name="puntos", type="integer", nullable=true)
     */
    private $puntos;



    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nombre.
     *
     * @param string|null $nombre
     *
     * @return AdminMedallas
     */
    public function setNombre($nombre = null)
    {
        $this->nombre = $nombre;
    
        return $this;
    }

    /**
     * Get nombre.
     *
     * @return string|null
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set descripcion.
     *
     * @param string|null $descripcion
     *
     * @return AdminMedallas
     */
    public function setDescripcion($descripcion = null)
    {
        $this->descripcion = $descripcion;
    
        return $this;
    }

    /**
     * Get descripcion.
     *
     * @return string|null
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set puntos.
     *
     * @param int|null $puntos
     *
     * @return AdminMedallas
     */
    public function setPuntos($puntos = null)
    {
        $this->puntos = $puntos;
    
        return $this;
    }

    /**
     * Get puntos.
     *
     * @return int|null
     */
    public function getPuntos()
    {
        return $this->puntos;
    }
    /**
     * @var int|null
     */
    private $categoria;


    /**
     * Set categoria.
     *
     * @param int|null $categoria
     *
     * @return AdminMedallas
     */
    public function setCategoria($categoria = null)
    {
        $this->categoria = $categoria;
    
        return $this;
    }

    /**
     * Get categoria.
     *
     * @return int|null
     */
    public function getCategoria()
    {
        return $this->categoria;
    }
}
