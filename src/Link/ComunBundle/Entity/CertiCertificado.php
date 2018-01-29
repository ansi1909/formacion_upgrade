<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CertiCertificado
 *
 * @ORM\Table(name="certi_certificado", indexes={@ORM\Index(name="IDX_D0147FE7521E1991", columns={"empresa_id"}), @ORM\Index(name="IDX_D0147FE71AAC87BB", columns={"tipo_certificado_id"}), @ORM\Index(name="IDX_D0147FE77649AC72", columns={"tipo_imagen_certificado_id"})})
 * @ORM\Entity
 */
class CertiCertificado
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="certi_certificado_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="entidad_id", type="integer", nullable=true)
     */
    private $entidadId;

    /**
     * @var string
     *
     * @ORM\Column(name="imagen", type="string", length=250, nullable=true)
     */
    private $imagen;

    /**
     * @var string
     *
     * @ORM\Column(name="encabezado", type="text", nullable=true)
     */
    private $encabezado;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="text", nullable=true)
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
     * @ORM\Column(name="titulo", type="text", nullable=true)
     */
    private $titulo;

    /**
     * @var string
     *
     * @ORM\Column(name="fecha", type="text", nullable=true)
     */
    private $fecha;

    /**
     * @var string
     *
     * @ORM\Column(name="qr", type="text", nullable=true)
     */
    private $qr;

    /**
     * @var \Link\ComunBundle\Entity\AdminEmpresa
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\AdminEmpresa")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="empresa_id", referencedColumnName="id")
     * })
     */
    private $empresa;

    /**
     * @var \Link\ComunBundle\Entity\CertiTipoCertificado
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\CertiTipoCertificado")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_certificado_id", referencedColumnName="id")
     * })
     */
    private $tipoCertificado;

    /**
     * @var \Link\ComunBundle\Entity\CertiTipoImagenCertificado
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\CertiTipoImagenCertificado")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_imagen_certificado_id", referencedColumnName="id")
     * })
     */
    private $tipoImagenCertificado;



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
     * Set entidadId
     *
     * @param integer $entidadId
     *
     * @return CertiCertificado
     */
    public function setEntidadId($entidadId)
    {
        $this->entidadId = $entidadId;
    
        return $this;
    }

    /**
     * Get entidadId
     *
     * @return integer
     */
    public function getEntidadId()
    {
        return $this->entidadId;
    }

    /**
     * Set imagen
     *
     * @param string $imagen
     *
     * @return CertiCertificado
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
     * Set encabezado
     *
     * @param string $encabezado
     *
     * @return CertiCertificado
     */
    public function setEncabezado($encabezado)
    {
        $this->encabezado = $encabezado;
    
        return $this;
    }

    /**
     * Get encabezado
     *
     * @return string
     */
    public function getEncabezado()
    {
        return $this->encabezado;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     *
     * @return CertiCertificado
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
     * @return CertiCertificado
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
     * Set titulo
     *
     * @param string $titulo
     *
     * @return CertiCertificado
     */
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;
    
        return $this;
    }

    /**
     * Get titulo
     *
     * @return string
     */
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * Set fecha
     *
     * @param string $fecha
     *
     * @return CertiCertificado
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    
        return $this;
    }

    /**
     * Get fecha
     *
     * @return string
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set qr
     *
     * @param string $qr
     *
     * @return CertiCertificado
     */
    public function setQr($qr)
    {
        $this->qr = $qr;
    
        return $this;
    }

    /**
     * Get qr
     *
     * @return string
     */
    public function getQr()
    {
        return $this->qr;
    }

    /**
     * Set empresa
     *
     * @param \Link\ComunBundle\Entity\AdminEmpresa $empresa
     *
     * @return CertiCertificado
     */
    public function setEmpresa(\Link\ComunBundle\Entity\AdminEmpresa $empresa = null)
    {
        $this->empresa = $empresa;
    
        return $this;
    }

    /**
     * Get empresa
     *
     * @return \Link\ComunBundle\Entity\AdminEmpresa
     */
    public function getEmpresa()
    {
        return $this->empresa;
    }

    /**
     * Set tipoCertificado
     *
     * @param \Link\ComunBundle\Entity\CertiTipoCertificado $tipoCertificado
     *
     * @return CertiCertificado
     */
    public function setTipoCertificado(\Link\ComunBundle\Entity\CertiTipoCertificado $tipoCertificado = null)
    {
        $this->tipoCertificado = $tipoCertificado;
    
        return $this;
    }

    /**
     * Get tipoCertificado
     *
     * @return \Link\ComunBundle\Entity\CertiTipoCertificado
     */
    public function getTipoCertificado()
    {
        return $this->tipoCertificado;
    }

    /**
     * Set tipoImagenCertificado
     *
     * @param \Link\ComunBundle\Entity\CertiTipoImagenCertificado $tipoImagenCertificado
     *
     * @return CertiCertificado
     */
    public function setTipoImagenCertificado(\Link\ComunBundle\Entity\CertiTipoImagenCertificado $tipoImagenCertificado = null)
    {
        $this->tipoImagenCertificado = $tipoImagenCertificado;
    
        return $this;
    }

    /**
     * Get tipoImagenCertificado
     *
     * @return \Link\ComunBundle\Entity\CertiTipoImagenCertificado
     */
    public function getTipoImagenCertificado()
    {
        return $this->tipoImagenCertificado;
    }
}
