<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CertiPreguntaAsociacion
 *
 * @ORM\Table(name="certi_pregunta_asociacion", indexes={@ORM\Index(name="pregunta_asociacion_ndx1", columns={"pregunta_id"})})
 * @ORM\Entity
 */
class CertiPreguntaAsociacion
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="certi_pregunta_asociacion_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="preguntas", type="string", length=50, nullable=true)
     */
    private $preguntas;

    /**
     * @var string
     *
     * @ORM\Column(name="opciones", type="string", length=50, nullable=true)
     */
    private $opciones;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set preguntas
     *
     * @param string $preguntas
     *
     * @return CertiPreguntaAsociacion
     */
    public function setPreguntas($preguntas)
    {
        $this->preguntas = $preguntas;

        return $this;
    }

    /**
     * Get preguntas
     *
     * @return string
     */
    public function getPreguntas()
    {
        return $this->preguntas;
    }

    /**
     * Set opciones
     *
     * @param string $opciones
     *
     * @return CertiPreguntaAsociacion
     */
    public function setOpciones($opciones)
    {
        $this->opciones = $opciones;

        return $this;
    }

    /**
     * Get opciones
     *
     * @return string
     */
    public function getOpciones()
    {
        return $this->opciones;
    }

    /**
     * Set pregunta
     *
     * @param \Link\ComunBundle\Entity\CertiPregunta $pregunta
     *
     * @return CertiPreguntaAsociacion
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
}
