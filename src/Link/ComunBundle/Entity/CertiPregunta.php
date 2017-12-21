<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CertiPregunta
 *
 * @ORM\Table(name="certi_pregunta", indexes={@ORM\Index(name="pregunta_ndx1", columns={"prueba_id", "tipo_elemento_id"}), @ORM\Index(name="IDX_FF8A1BE1E7DE889A", columns={"prueba_id"}), @ORM\Index(name="IDX_FF8A1BE1481AEE6", columns={"tipo_pregunta_id"}), @ORM\Index(name="IDX_FF8A1BE1F4868001", columns={"tipo_elemento_id"}), @ORM\Index(name="IDX_FF8A1BE1DB38439E", columns={"usuario_id"}), @ORM\Index(name="IDX_FF8A1BE164373B63", columns={"estatus_contenido_id"}), @ORM\Index(name="IDX_FF8A1BE131A5801E", columns={"pregunta_id"})})
 * @ORM\Entity
 */
class CertiPregunta
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="certi_pregunta_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="enunciado", type="string", length=500, nullable=true)
     */
    private $enunciado;

    /**
     * @var string
     *
     * @ORM\Column(name="imagen", type="string", length=500, nullable=true)
     */
    private $imagen;

    /**
     * @var string
     *
     * @ORM\Column(name="valor", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $valor;

    /**
     * @var \Link\ComunBundle\Entity\CertiPrueba
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\CertiPrueba")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prueba_id", referencedColumnName="id")
     * })
     */
    private $prueba;

    /**
     * @var \Link\ComunBundle\Entity\CertiTipoPregunta
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\CertiTipoPregunta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_pregunta_id", referencedColumnName="id")
     * })
     */
    private $tipoPregunta;

    /**
     * @var \Link\ComunBundle\Entity\CertiTipoElemento
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\CertiTipoElemento")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_elemento_id", referencedColumnName="id")
     * })
     */
    private $tipoElemento;

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
     * @var \Link\ComunBundle\Entity\CertiPregunta
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\CertiPregunta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pregunta_id", referencedColumnName="id")
     * })
     */
    private $pregunta;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set enunciado
     *
     * @param string $enunciado
     *
     * @return CertiPregunta
     */
    public function setEnunciado($enunciado)
    {
        $this->enunciado = $enunciado;

        return $this;
    }

    /**
     * Get enunciado
     *
     * @return string
     */
    public function getEnunciado()
    {
        return $this->enunciado;
    }

    /**
     * Set imagen
     *
     * @param string $imagen
     *
     * @return CertiPregunta
     */
    public function setImagen($imagen)
    {
        $this->imagen = $imagen;

        return $this;
    }

    /**
     * Get imagen
     *
     * @return string
     */
    public function getImagen()
    {
        return $this->imagen;
    }

    /**
     * Set valor
     *
     * @param string $valor
     *
     * @return CertiPregunta
     */
    public function setValor($valor)
    {
        $this->valor = $valor;

        return $this;
    }

    /**
     * Get valor
     *
     * @return string
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * Set prueba
     *
     * @param \Link\ComunBundle\Entity\CertiPrueba $prueba
     *
     * @return CertiPregunta
     */
    public function setPrueba(\Link\ComunBundle\Entity\CertiPrueba $prueba = null)
    {
        $this->prueba = $prueba;

        return $this;
    }

    /**
     * Get prueba
     *
     * @return \Link\ComunBundle\Entity\CertiPrueba
     */
    public function getPrueba()
    {
        return $this->prueba;
    }

    /**
     * Set tipoPregunta
     *
     * @param \Link\ComunBundle\Entity\CertiTipoPregunta $tipoPregunta
     *
     * @return CertiPregunta
     */
    public function setTipoPregunta(\Link\ComunBundle\Entity\CertiTipoPregunta $tipoPregunta = null)
    {
        $this->tipoPregunta = $tipoPregunta;

        return $this;
    }

    /**
     * Get tipoPregunta
     *
     * @return \Link\ComunBundle\Entity\CertiTipoPregunta
     */
    public function getTipoPregunta()
    {
        return $this->tipoPregunta;
    }

    /**
     * Set tipoElemento
     *
     * @param \Link\ComunBundle\Entity\CertiTipoElemento $tipoElemento
     *
     * @return CertiPregunta
     */
    public function setTipoElemento(\Link\ComunBundle\Entity\CertiTipoElemento $tipoElemento = null)
    {
        $this->tipoElemento = $tipoElemento;

        return $this;
    }

    /**
     * Get tipoElemento
     *
     * @return \Link\ComunBundle\Entity\CertiTipoElemento
     */
    public function getTipoElemento()
    {
        return $this->tipoElemento;
    }

    /**
     * Set usuario
     *
     * @param \Link\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return CertiPregunta
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
     * @return CertiPregunta
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
     * Set pregunta
     *
     * @param \Link\ComunBundle\Entity\CertiPregunta $pregunta
     *
     * @return CertiPregunta
     */
    public function setPregunta(\Link\ComunBundle\Entity\CertiPregunta $pregunta = null)
    {
        $this->pregunta = $pregunta;

        return $this;
    }

    /**
     * Get pregunta
     *
     * @return \Link\ComunBundle\Entity\CertiPregunta
     */
    public function getPregunta()
    {
        return $this->pregunta;
    }

    /**
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     *
     * @return CertiPregunta
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
     * @return CertiPregunta
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
     * @return CertiPregunta
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
