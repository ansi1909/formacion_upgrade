<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminPais
 *
 * @ORM\Table(name="admin_pais")
 * @ORM\Entity
 */
class AdminPais
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="string", length=3)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_pais_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=52, nullable=true)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="continente", type="string", length=100, nullable=true)
     */
    private $continente;

    /**
     * @var string
     *
     * @ORM\Column(name="region", type="string", length=26, nullable=true)
     */
    private $region;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre_local", type="string", length=45, nullable=true)
     */
    private $nombreLocal;

    /**
     * @var integer
     *
     * @ORM\Column(name="capital", type="integer", nullable=true)
     */
    private $capital;

    /**
     * @var string
     *
     * @ORM\Column(name="id2", type="string", length=2, nullable=true)
     */
    private $id2;



    /**
     * Get id
     *
     * @return string
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
     * @return AdminPais
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
     * Set continente
     *
     * @param string $continente
     *
     * @return AdminPais
     */
    public function setContinente($continente)
    {
        $this->continente = $continente;
    
        return $this;
    }

    /**
     * Get continente
     *
     * @return string
     */
    public function getContinente()
    {
        return $this->continente;
    }

    /**
     * Set region
     *
     * @param string $region
     *
     * @return AdminPais
     */
    public function setRegion($region)
    {
        $this->region = $region;
    
        return $this;
    }

    /**
     * Get region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set nombreLocal
     *
     * @param string $nombreLocal
     *
     * @return AdminPais
     */
    public function setNombreLocal($nombreLocal)
    {
        $this->nombreLocal = $nombreLocal;
    
        return $this;
    }

    /**
     * Get nombreLocal
     *
     * @return string
     */
    public function getNombreLocal()
    {
        return $this->nombreLocal;
    }

    /**
     * Set capital
     *
     * @param integer $capital
     *
     * @return AdminPais
     */
    public function setCapital($capital)
    {
        $this->capital = $capital;
    
        return $this;
    }

    /**
     * Get capital
     *
     * @return integer
     */
    public function getCapital()
    {
        return $this->capital;
    }

    /**
     * Set id2
     *
     * @param string $id2
     *
     * @return AdminPais
     */
    public function setId2($id2)
    {
        $this->id2 = $id2;
    
        return $this;
    }

    /**
     * Get id2
     *
     * @return string
     */
    public function getId2()
    {
        return $this->id2;
    }
}
