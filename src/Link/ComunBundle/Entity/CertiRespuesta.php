<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CertiRespuesta
 *
 * @ORM\Table(name="certi_respuesta", indexes={@ORM\Index(name="IDX_98EB1A4531A5801E", columns={"pregunta_id"}), @ORM\Index(name="IDX_98EB1A455BDBF2F", columns={"opcion_id"}), @ORM\Index(name="IDX_98EB1A4525A67894", columns={"prueba_log_id"})})
 * @ORM\Entity
 */
class CertiRespuesta
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="certi_respuesta_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_registro", type="datetime", nullable=true)
     */
    private $fechaRegistro;

    /**
     * @var integer
     *
     * @ORM\Column(name="nro", type="integer", nullable=true)
     */
    private $nro;

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
     * @var \Link\ComunBundle\Entity\CertiOpcion
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\CertiOpcion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="opcion_id", referencedColumnName="id")
     * })
     */
    private $opcion;

    /**
     * @var \Link\ComunBundle\Entity\CertiPruebaLog
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\CertiPruebaLog")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prueba_log_id", referencedColumnName="id")
     * })
     */
    private $pruebaLog;



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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     *
     * @return CertiRespuesta
     */
    public function setFechaRegistro($fechaRegistro)
    {
        $this->fechaRegistro = $fechaRegistro;
    
        return $this;
    }

    /**
     * Get fechaRegistro
     *
     * @return \DateTime
     */
    public function getFechaRegistro()
    {
        return $this->fechaRegistro;
    }

    /**
     * Set nro
     *
     * @param integer $nro
     *
     * @return CertiRespuesta
     */
    public function setNro($nro)
    {
        $this->nro = $nro;
    
        return $this;
    }

    /**
     * Get nro
     *
     * @return integer
     */
    public function getNro()
    {
        return $this->nro;
    }

    /**
     * Set pregunta
     *
     * @param \Link\ComunBundle\Entity\CertiPregunta $pregunta
     *
     * @return CertiRespuesta
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
     * Set opcion
     *
     * @param \Link\ComunBundle\Entity\CertiOpcion $opcion
     *
     * @return CertiRespuesta
     */
    public function setOpcion(\Link\ComunBundle\Entity\CertiOpcion $opcion = null)
    {
        $this->opcion = $opcion;
    
        return $this;
    }

    /**
     * Get opcion
     *
     * @return \Link\ComunBundle\Entity\CertiOpcion
     */
    public function getOpcion()
    {
        return $this->opcion;
    }

    /**
     * Set pruebaLog
     *
     * @param \Link\ComunBundle\Entity\CertiPruebaLog $pruebaLog
     *
     * @return CertiRespuesta
     */
    public function setPruebaLog(\Link\ComunBundle\Entity\CertiPruebaLog $pruebaLog = null)
    {
        $this->pruebaLog = $pruebaLog;
    
        return $this;
    }

    /**
     * Get pruebaLog
     *
     * @return \Link\ComunBundle\Entity\CertiPruebaLog
     */
    public function getPruebaLog()
    {
        return $this->pruebaLog;
    }
}
