<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CertiPrueba
 *
 * @ORM\Table(name="certi_prueba", indexes={@ORM\Index(name="prueba_ndx1", columns={"pagina_id"}), @ORM\Index(name="IDX_B7E318BFDB38439E", columns={"usuario_id"}), @ORM\Index(name="IDX_B7E318BF64373B63", columns={"estatus_contenido_id"})})
 * @ORM\Entity
 */
class CertiPrueba
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="certi_prueba_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=350, nullable=true)
     */
    private $nombre;

    /**
     * @var integer
     *
     * @ORM\Column(name="cantidad_preguntas", type="integer", nullable=true)
     */
    private $cantidadPreguntas;

    /**
     * @var integer
     *
     * @ORM\Column(name="cantidad_mostrar", type="integer", nullable=true)
     */
    private $cantidadMostrar;

    /**
     * @var integer
     *
     * @ORM\Column(name="duracion", type="time", nullable=true)
     */
    private $duracion;

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
     * @return CertiPrueba
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
     * Set cantidadPreguntas
     *
     * @param integer $cantidadPreguntas
     *
     * @return CertiPrueba
     */
    public function setCantidadPreguntas($cantidadPreguntas)
    {
        $this->cantidadPreguntas = $cantidadPreguntas;

        return $this;
    }

    /**
     * Get cantidadPreguntas
     *
     * @return integer
     */
    public function getCantidadPreguntas()
    {
        return $this->cantidadPreguntas;
    }

    /**
     * Set cantidadMostrar
     *
     * @param integer $cantidadMostrar
     *
     * @return CertiPrueba
     */
    public function setCantidadMostrar($cantidadMostrar)
    {
        $this->cantidadMostrar = $cantidadMostrar;

        return $this;
    }

    /**
     * Get cantidadMostrar
     *
     * @return integer
     */
    public function getCantidadMostrar()
    {
        return $this->cantidadMostrar;
    }

    /**
     * Set duracion
     *
     * @param \DateTime $duracion
     *
     * @return CertiPrueba
     */
    public function setDuracion($duracion)
    {
        $this->duracion = $duracion;

        return $this;
    }

    /**
     * Get duracion
     *
     * @return \DateTime
     */
    public function getDuracion()
    {
        return $this->duracion;
    }

    /**
     * Set pagina
     *
     * @param \Link\ComunBundle\Entity\CertiPagina $pagina
     *
     * @return CertiPrueba
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
     * Set usuario
     *
     * @param \Link\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return CertiPrueba
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
     * @return CertiPrueba
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

     /**
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     *
     * @return CertiPrueba
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
     * @return CertiPrueba
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
}
