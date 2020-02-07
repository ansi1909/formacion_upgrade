<?php

namespace Link\ComunBundle\Entity;

/**
 * AdminZonaHoraria
 */
class AdminZonaHoraria
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nombre;

    /**
     * @var \Link\ComunBundle\Entity\AdminPais
     */
    private $pais;


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
     * @return AdminZonaHoraria
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
     * Set pais
     *
     * @param \Link\ComunBundle\Entity\AdminPais $pais
     *
     * @return AdminZonaHoraria
     */
    public function setPais(\Link\ComunBundle\Entity\AdminPais $pais = null)
    {
        $this->pais = $pais;
    
        return $this;
    }

    /**
     * Get pais
     *
     * @return \Link\ComunBundle\Entity\AdminPais
     */
    public function getPais()
    {
        return $this->pais;
    }
    /**
     * @var integer
     */
    private $orden;


    /**
     * Set orden
     *
     * @param integer $orden
     *
     * @return AdminZonaHoraria
     */
    public function setOrden($orden)
    {
        $this->orden = $orden;
    
        return $this;
    }

    /**
     * Get orden
     *
     * @return integer
     */
    public function getOrden()
    {
        return $this->orden;
    }
}
