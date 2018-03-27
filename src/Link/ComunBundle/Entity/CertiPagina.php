<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CertiPagina
 *
 * @ORM\Table(name="certi_pagina", indexes={@ORM\Index(name="IDX_CF1CDC9157991ECF", columns={"pagina_id"}), @ORM\Index(name="IDX_CF1CDC913397707A", columns={"categoria_id"}), @ORM\Index(name="IDX_CF1CDC91DB38439E", columns={"usuario_id"}), @ORM\Index(name="IDX_CF1CDC9164373B63", columns={"estatus_contenido_id"})})
 * @ORM\Entity
 */
class CertiPagina
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="certi_pagina_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=500, nullable=true)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="text", nullable=true)
     */
    private $descripcion;

    /**
     * @var string
     *
     * @ORM\Column(name="contenido", type="text", nullable=true)
     */
    private $contenido;

    /**
     * @var string
     *
     * @ORM\Column(name="foto", type="string", length=250, nullable=true)
     */
    private $foto;

    /**
     * @var string
     *
     * @ORM\Column(name="pdf", type="string", length=250, nullable=true)
     */
    private $pdf;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_creacion", type="datetime", nullable=true)
     */
    private $fechaCreacion;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_modificacion", type="datetime", nullable=true)
     */
    private $fechaModificacion;

    /**
     * @var integer
     *
     * @ORM\Column(name="orden", type="integer", nullable=true)
     */
    private $orden;

    /**
     * @var string
     *
     * @ORM\Column(name="encuesta", type="text", nullable=true)
     */
    private $encuesta;

    /**
     * @var \Link\ComunBundle\Entity\CertiPagina
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\CertiPagina")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pagina_id", referencedColumnName="id")
     * })
     */
    private $pagina;

    /**
     * @var \Link\ComunBundle\Entity\CertiCategoria
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\CertiCategoria")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="categoria_id", referencedColumnName="id")
     * })
     */
    private $categoria;

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
     * @var \Link\ComunBundle\Entity\CertiEstatusContenido
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\CertiEstatusContenido")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="estatus_contenido_id", referencedColumnName="id")
     * })
     */
    private $estatusContenido;



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
     * @return CertiPagina
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
     * @return CertiPagina
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
     * Set contenido
     *
     * @param string $contenido
     *
     * @return CertiPagina
     */
    public function setContenido($contenido)
    {
        $this->contenido = $contenido;
    
        return $this;
    }

    /**
     * Get contenido
     *
     * @return string
     */
    public function getContenido()
    {
        return $this->contenido;
    }

    /**
     * Set foto
     *
     * @param string $foto
     *
     * @return CertiPagina
     */
    public function setFoto($foto)
    {
        $this->foto = $foto;
    
        return $this;
    }

    /**
     * Get foto
     *
     * @return string
     */
    public function getFoto()
    {
        return $this->foto;
    }

    /**
     * Set pdf
     *
     * @param string $pdf
     *
     * @return CertiPagina
     */
    public function setPdf($pdf)
    {
        $this->pdf = $pdf;
    
        return $this;
    }

    /**
     * Get pdf
     *
     * @return string
     */
    public function getPdf()
    {
        return $this->pdf;
    }

    /**
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     *
     * @return CertiPagina
     */
    public function setFechaCreacion($fechaCreacion)
    {
        $this->fechaCreacion = $fechaCreacion;
    
        return $this;
    }

    /**
     * Get fechaCreacion
     *
     * @return \DateTime
     */
    public function getFechaCreacion()
    {
        return $this->fechaCreacion;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     *
     * @return CertiPagina
     */
    public function setFechaModificacion($fechaModificacion)
    {
        $this->fechaModificacion = $fechaModificacion;
    
        return $this;
    }

    /**
     * Get fechaModificacion
     *
     * @return \DateTime
     */
    public function getFechaModificacion()
    {
        return $this->fechaModificacion;
    }

    /**
     * Set orden
     *
     * @param integer $orden
     *
     * @return CertiPagina
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
     * Set encuesta
     *
     * @param string $encuesta
     *
     * @return CertiPagina
     */
    public function setEncuesta($encuesta)
    {
        $this->encuesta = $encuesta;
    
        return $this;
    }

    /**
     * Get encuesta
     *
     * @return string
     */
    public function getEncuesta()
    {
        return $this->encuesta;
    }

    /**
     * Set pagina
     *
     * @param \Link\ComunBundle\Entity\CertiPagina $pagina
     *
     * @return CertiPagina
     */
    public function setPagina(\Link\ComunBundle\Entity\CertiPagina $pagina = null)
    {
        $this->pagina = $pagina;
    
        return $this;
    }

    /**
     * Get pagina
     *
     * @return \Link\ComunBundle\Entity\CertiPagina
     */
    public function getPagina()
    {
        return $this->pagina;
    }

    /**
     * Set categoria
     *
     * @param \Link\ComunBundle\Entity\CertiCategoria $categoria
     *
     * @return CertiPagina
     */
    public function setCategoria(\Link\ComunBundle\Entity\CertiCategoria $categoria = null)
    {
        $this->categoria = $categoria;
    
        return $this;
    }

    /**
     * Get categoria
     *
     * @return \Link\ComunBundle\Entity\CertiCategoria
     */
    public function getCategoria()
    {
        return $this->categoria;
    }

    /**
     * Set usuario
     *
     * @param \Link\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return CertiPagina
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
     * Set estatusContenido
     *
     * @param \Link\ComunBundle\Entity\CertiEstatusContenido $estatusContenido
     *
     * @return CertiPagina
     */
    public function setEstatusContenido(\Link\ComunBundle\Entity\CertiEstatusContenido $estatusContenido = null)
    {
        $this->estatusContenido = $estatusContenido;
    
        return $this;
    }

    /**
     * Get estatusContenido
     *
     * @return \Link\ComunBundle\Entity\CertiEstatusContenido
     */
    public function getEstatusContenido()
    {
        return $this->estatusContenido;
    }
}
