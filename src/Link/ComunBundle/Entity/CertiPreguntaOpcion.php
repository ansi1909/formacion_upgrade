<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CertiPreguntaOpcion
 *
 * @ORM\Table(name="certi_pregunta_opcion", indexes={@ORM\Index(name="IDX_87EE792C31A5801E", columns={"pregunta_id"}), @ORM\Index(name="IDX_87EE792C5BDBF2F", columns={"opcion_id"})})
 * @ORM\Entity
 */
class CertiPreguntaOpcion
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="certi_pregunta_opcion_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var boolean
     *
     * @ORM\Column(name="correcta", type="boolean", nullable=true)
     */
    private $correcta;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set correcta
     *
     * @param boolean $correcta
     *
     * @return CertiPreguntaOpcion
     */
    public function setCorrecta($correcta)
    {
        $this->correcta = $correcta;
    
        return $this;
    }

    /**
     * Get correcta
     *
     * @return boolean
     */
    public function getCorrecta()
    {
        return $this->correcta;
    }

    /**
     * Set pregunta
     *
     * @param \Link\ComunBundle\Entity\CertiPregunta $pregunta
     *
     * @return CertiPreguntaOpcion
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
     * @return CertiPreguntaOpcion
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
}
