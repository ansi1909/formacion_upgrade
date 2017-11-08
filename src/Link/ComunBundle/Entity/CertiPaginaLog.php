<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CertiPaginaLog
 *
 * @ORM\Table(name="certi_pagina_log", indexes={@ORM\Index(name="pagina_log_ndx1", columns={"pagina_id", "usuario_id"}), @ORM\Index(name="IDX_AE97B9D657991ECF", columns={"pagina_id"}), @ORM\Index(name="IDX_AE97B9D6DB38439E", columns={"usuario_id"}), @ORM\Index(name="IDX_AE97B9D67D139FE4", columns={"estatus_pagina_id"})})
 * @ORM\Entity
 */
class CertiPaginaLog
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="certi_pagina_log_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_inicio", type="datetime", nullable=true)
     */
    private $fechaInicio;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_fin", type="datetime", nullable=true)
     */
    private $fechaFin;

    /**
     * @var string
     *
     * @ORM\Column(name="porcentaje_avance", type="decimal", precision=3, scale=2, nullable=true)
     */
    private $porcentajeAvance;

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
     * @var \Link\ComunBundle\Entity\CertiEstatusPagina
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\CertiEstatusPagina")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="estatus_pagina_id", referencedColumnName="id")
     * })
     */
    private $estatusPagina;



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
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     *
     * @return CertiPaginaLog
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
     * Set fechaFin
     *
     * @param \DateTime $fechaFin
     *
     * @return CertiPaginaLog
     */
    public function setFechaFin($fechaFin)
    {
        $this->fechaFin = $fechaFin;

        return $this;
    }

    /**
     * Get fechaFin
     *
     * @return \DateTime
     */
    public function getFechaFin()
    {
        return $this->fechaFin;
    }

    /**
     * Set porcentajeAvance
     *
     * @param string $porcentajeAvance
     *
     * @return CertiPaginaLog
     */
    public function setPorcentajeAvance($porcentajeAvance)
    {
        $this->porcentajeAvance = $porcentajeAvance;

        return $this;
    }

    /**
     * Get porcentajeAvance
     *
     * @return string
     */
    public function getPorcentajeAvance()
    {
        return $this->porcentajeAvance;
    }

    /**
     * Set pagina
     *
     * @param \Link\ComunBundle\Entity\CertiPagina $pagina
     *
     * @return CertiPaginaLog
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
     * @return CertiPaginaLog
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
     * Set estatusPagina
     *
     * @param \Link\ComunBundle\Entity\CertiEstatusPagina $estatusPagina
     *
     * @return CertiPaginaLog
     */
    public function setEstatusPagina(\Link\ComunBundle\Entity\CertiEstatusPagina $estatusPagina = null)
    {
        $this->estatusPagina = $estatusPagina;

        return $this;
    }

    /**
     * Get estatusPagina
     *
     * @return \Link\ComunBundle\Entity\CertiEstatusPagina
     */
    public function getEstatusPagina()
    {
        return $this->estatusPagina;
    }
}
