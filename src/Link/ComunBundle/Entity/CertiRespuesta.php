<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CertiRespuesta
 *
 * @ORM\Table(name="certi_respuesta", indexes={@ORM\Index(name="IDX_98EB1A4531A5801E", columns={"pregunta_id"}), @ORM\Index(name="IDX_98EB1A455BDBF2F", columns={"pregunta_opcion_id"}), @ORM\Index(name="IDX_98EB1A45DB38439E", columns={"usuario_id"})})
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
     * @var \Link\ComunBundle\Entity\CertiPregunta
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\CertiPregunta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pregunta_id", referencedColumnName="id")
     * })
     */
    private $pregunta;

    /**
     * @var \Link\ComunBundle\Entity\CertiPreguntaOpcion
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\CertiPreguntaOpcion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pregunta_opcion_id", referencedColumnName="id")
     * })
     */
    private $preguntaOpcion;

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
     * Set preguntaOpcion
     *
     * @param \Link\ComunBundle\Entity\CertiPreguntaOpcion $opcion
     *
     * @return CertiRespuesta
     */
    public function setPreguntaOpcion(\Link\ComunBundle\Entity\CertiPreguntaOpcion $preguntaOpcion = null)
    {
        $this->preguntaOpcion = $preguntaOpcion;

        return $this;
    }

    /**
     * Get preguntaOpcion
     *
     * @return \Link\ComunBundle\Entity\CertiPreguntaOpcion
     */
    public function getPreguntaOpcion()
    {
        return $this->preguntaOpcion;
    }

    /**
     * Set usuario
     *
     * @param \Link\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return CertiRespuesta
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
