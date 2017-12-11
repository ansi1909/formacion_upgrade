<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CertiPaginaEmpresa
 *
 * @ORM\Table(name="certi_pagina_empresa", indexes={@ORM\Index(name="pagina_empresa_ndx1", columns={"empresa_id", "pagina_id"}), @ORM\Index(name="IDX_5C87DB99521E1991", columns={"empresa_id"}), @ORM\Index(name="IDX_5C87DB9957991ECF", columns={"pagina_id"})})
 * @ORM\Entity
 */
class CertiPaginaEmpresa
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="certi_pagina_empresa_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var boolean
     *
     * @ORM\Column(name="activo", type="boolean", nullable=true)
     */
    private $activo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_inicio", type="date", nullable=true)
     */
    private $fechaInicio;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_vencimiento", type="date", nullable=true)
     */
    private $fechaVencimiento;

    /**
     * @var boolean
     *
     * @ORM\Column(name="prueba_activa", type="boolean", nullable=true)
     */
    private $pruebaActiva;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_intentos", type="integer", nullable=true)
     */
    private $maxIntentos;

    /**
     * @var string
     *
     * @ORM\Column(name="puntaje_aprueba", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $puntajeAprueba;

    /**
     * @var boolean
     *
     * @ORM\Column(name="muro_activo", type="boolean", nullable=true)
     */
    private $muroActivo;

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
     * @var \Link\ComunBundle\Entity\CertiPagina
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\CertiPagina")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pagina_id", referencedColumnName="id")
     * })
     */
    private $pagina;

    /**
     * @var string
     *
     * @ORM\Column(name="imagen_certificado", type="string", length=250, nullable=true)
     */
    private $imagenCertificado;



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
     * Set activo
     *
     * @param boolean $activo
     *
     * @return CertiPaginaEmpresa
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
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     *
     * @return CertiPaginaEmpresa
     */
    public function setFechaInicio($fechaInicio)
    {
        $this->fechaInicio = $fechaInicio;

        return $this;
    }

    /**
     * Get fechaInicio
     *
     * @return \DateTime
     */
    public function getFechaInicio()
    {
        return $this->fechaInicio;
    }

    /**
     * Set fechaVencimiento
     *
     * @param \DateTime $fechaVencimiento
     *
     * @return CertiPaginaEmpresa
     */
    public function setFechaVencimiento($fechaVencimiento)
    {
        $this->fechaVencimiento = $fechaVencimiento;

        return $this;
    }

    /**
     * Get fechaVencimiento
     *
     * @return \DateTime
     */
    public function getFechaVencimiento()
    {
        return $this->fechaVencimiento;
    }

    /**
     * Set pruebaActiva
     *
     * @param boolean $pruebaActiva
     *
     * @return CertiPaginaEmpresa
     */
    public function setPruebaActiva($pruebaActiva)
    {
        $this->pruebaActiva = $pruebaActiva;

        return $this;
    }

    /**
     * Get pruebaActiva
     *
     * @return boolean
     */
    public function getPruebaActiva()
    {
        return $this->pruebaActiva;
    }

    /**
     * Set maxIntentos
     *
     * @param integer $maxIntentos
     *
     * @return CertiPaginaEmpresa
     */
    public function setMaxIntentos($maxIntentos)
    {
        $this->maxIntentos = $maxIntentos;

        return $this;
    }

    /**
     * Get maxIntentos
     *
     * @return integer
     */
    public function getMaxIntentos()
    {
        return $this->maxIntentos;
    }

    /**
     * Set puntajeAprueba
     *
     * @param string $puntajeAprueba
     *
     * @return CertiPaginaEmpresa
     */
    public function setPuntajeAprueba($puntajeAprueba)
    {
        $this->puntajeAprueba = $puntajeAprueba;

        return $this;
    }

    /**
     * Get puntajeAprueba
     *
     * @return string
     */
    public function getPuntajeAprueba()
    {
        return $this->puntajeAprueba;
    }

    /**
     * Set muroActivo
     *
     * @param boolean $muroActivo
     *
     * @return CertiPaginaEmpresa
     */
    public function setMuroActivo($muroActivo)
    {
        $this->muroActivo = $muroActivo;

        return $this;
    }

    /**
     * Get muroActivo
     *
     * @return boolean
     */
    public function getMuroActivo()
    {
        return $this->muroActivo;
    }

    /**
     * Set empresa
     *
     * @param \Link\ComunBundle\Entity\AdminEmpresa $empresa
     *
     * @return CertiPaginaEmpresa
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
     * Set pagina
     *
     * @param \Link\ComunBundle\Entity\CertiPagina $pagina
     *
     * @return CertiPaginaEmpresa
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
     * Set imagenCertificado
     *
     * @param string $imagenCertificado
     *
     * @return CertiPaginaEmpresa
     */
    public function setImagenCertificado($imagenCertificado)
    {
        $this->imagenCertificado = $imagenCertificado;

        return $this;
    }

    /**
     * Get imagenCertificado
     *
     * @return string
     */
    public function getImagenCertificado()
    {
        return $this->imagenCertificado;
    }
}
