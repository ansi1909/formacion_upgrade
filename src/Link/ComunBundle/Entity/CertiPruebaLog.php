<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CertiPruebaLog
 *
 * @ORM\Table(name="certi_prueba_log", indexes={@ORM\Index(name="IDX_B5265B3BE7DE889A", columns={"prueba_id"}), @ORM\Index(name="IDX_B5265B3BDB38439E", columns={"usuario_id"})})
 * @ORM\Entity
 */
class CertiPruebaLog
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="certi_prueba_log_id_seq", allocationSize=1, initialValue=1)
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
     * @var integer
     *
     * @ORM\Column(name="correctas", type="integer", nullable=true)
     */
    private $correctas;

    /**
     * @var integer
     *
     * @ORM\Column(name="erradas", type="integer", nullable=true)
     */
    private $erradas;

    /**
     * @var string
     *
     * @ORM\Column(name="nota", type="decimal", precision=3, scale=2, nullable=true)
     */
    private $nota;

    /**
     * @var string
     *
     * @ORM\Column(name="estado", type="string", length=15, nullable=true)
     */
    private $estado;

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
     * @var \Link\ComunBundle\Entity\AdminUsuario
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\AdminUsuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     * })
     */
    private $usuario;



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
     * @return CertiPruebaLog
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
     * @return CertiPruebaLog
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
     * @return CertiPruebaLog
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
     * Set correctas
     *
     * @param integer $correctas
     *
     * @return CertiPruebaLog
     */
    public function setCorrectas($correctas)
    {
        $this->correctas = $correctas;
    
        return $this;
    }

    /**
     * Get correctas
     *
     * @return integer
     */
    public function getCorrectas()
    {
        return $this->correctas;
    }

    /**
     * Set erradas
     *
     * @param integer $erradas
     *
     * @return CertiPruebaLog
     */
    public function setErradas($erradas)
    {
        $this->erradas = $erradas;
    
        return $this;
    }

    /**
     * Get erradas
     *
     * @return integer
     */
    public function getErradas()
    {
        return $this->erradas;
    }

    /**
     * Set nota
     *
     * @param string $nota
     *
     * @return CertiPruebaLog
     */
    public function setNota($nota)
    {
        $this->nota = $nota;
    
        return $this;
    }

    /**
     * Get nota
     *
     * @return string
     */
    public function getNota()
    {
        return $this->nota;
    }

    /**
     * Set estado
     *
     * @param string $estado
     *
     * @return CertiPruebaLog
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    
        return $this;
    }

    /**
     * Get estado
     *
     * @return string
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set prueba
     *
     * @param \Link\ComunBundle\Entity\CertiPrueba $prueba
     *
     * @return CertiPruebaLog
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
     * Set usuario
     *
     * @param \Link\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return CertiPruebaLog
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
}
