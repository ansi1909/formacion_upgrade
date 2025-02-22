<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminLigas
 *
 * @ORM\Table(name="admin_ligas")
 * @ORM\Entity
 */
class AdminLigas
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_ligas_id_seq", allocationSize=1, initialValue=1)
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
     * @ORM\Column(name="descripcion", type="string", length=500, nullable=true)
     */
    private $descripcion;

    /**
     * @var int|null
     *
     * @ORM\Column(name="puntuacion", type="integer", nullable=true)
     */
    private $puntuacion;



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
     * @return AdminLigas
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
     * @return AdminLigas
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
     * Set puntuacion.
     *
     * @param int|null $puntuacion
     *
     * @return AdminLigas
     */
    public function setPuntuacion($puntuacion = null)
    {
        $this->puntuacion = $puntuacion;
    
        return $this;
    }

    /**
     * Get puntuacion.
     *
     * @return int|null
     */
    public function getPuntuacion()
    {
        return $this->puntuacion;
    }
    /**
     * @var int|null
     */
    private $porcetajemin;

    /**
     * @var int|null
     */
    private $porcentajemax;


    /**
     * Set porcetajemin.
     *
     * @param int|null $porcetajemin
     *
     * @return AdminLigas
     */
    public function setPorcetajemin($porcetajemin = null)
    {
        $this->porcetajemin = $porcetajemin;
    
        return $this;
    }

    /**
     * Get porcetajemin.
     *
     * @return int|null
     */
    public function getPorcetajemin()
    {
        return $this->porcetajemin;
    }

    /**
     * Set porcentajemax.
     *
     * @param int|null $porcentajemax
     *
     * @return AdminLigas
     */
    public function setPorcentajemax($porcentajemax = null)
    {
        $this->porcentajemax = $porcentajemax;
    
        return $this;
    }

    /**
     * Get porcentajemax.
     *
     * @return int|null
     */
    public function getPorcentajemax()
    {
        return $this->porcentajemax;
    }
    /**
     * @var string|null
     */
    private $imagen;


    /**
     * Set imagen.
     *
     * @param string|null $imagen
     *
     * @return AdminLigas
     */
    public function setImagen($imagen = null)
    {
        $this->imagen = $imagen;
    
        return $this;
    }

    /**
     * Get imagen.
     *
     * @return string|null
     */
    public function getImagen()
    {
        return $this->imagen;
    }
    /**
     * @var int|null
     */
    private $porcentajemin;


    /**
     * Set porcentajemin.
     *
     * @param int|null $porcentajemin
     *
     * @return AdminLigas
     */
    public function setPorcentajemin($porcentajemin = null)
    {
        $this->porcentajemin = $porcentajemin;
    
        return $this;
    }

    /**
     * Get porcentajemin.
     *
     * @return int|null
     */
    public function getPorcentajemin()
    {
        return $this->porcentajemin;
    }
}
