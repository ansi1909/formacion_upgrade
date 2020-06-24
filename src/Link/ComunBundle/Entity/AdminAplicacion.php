<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminAplicacion
 *
 * @ORM\Table(name="admin_aplicacion", indexes={@ORM\Index(name="IDX_70906ED63AF686C8", columns={"aplicacion_id"})})
 * @ORM\Entity
 */
class AdminAplicacion
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_aplicacion_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=100, nullable=true)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=50, nullable=true)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="icono", type="string", length=20, nullable=true)
     */
    private $icono;

    /**
     * @var boolean
     *
     * @ORM\Column(name="activo", type="boolean", nullable=true)
     */
    private $activo;

    /**
     * @var integer
     *
     * @ORM\Column(name="orden", type="integer", nullable=true)
     */
    private $orden;

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
     * @return AdminAplicacion
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
     * Set url
     *
     * @param string $url
     *
     * @return AdminAplicacion
     */
    public function setUrl($url)
    {
        $this->url = $url;
    
        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set icono
     *
     * @param string $icono
     *
     * @return AdminAplicacion
     */
    public function setIcono($icono)
    {
        $this->icono = $icono;
    
        return $this;
    }

    /**
     * Get icono
     *
     * @return string
     */
    public function getIcono()
    {
        return $this->icono;
    }

    /**
     * Set activo
     *
     * @param boolean $activo
     *
     * @return AdminAplicacion
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;
    
        return $this;
    }

    /**
     * Get activo
     *
     * @return boolean
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * Set orden
     *
     * @param integer $orden
     *
     * @return AdminAplicacion
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

    /**
     * Set aplicacion
     *
     * @param \Link\ComunBundle\Entity\AdminAplicacion $aplicacion
     *
     * @return AdminAplicacion
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
}
