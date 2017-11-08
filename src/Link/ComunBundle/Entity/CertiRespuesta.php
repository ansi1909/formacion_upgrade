<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CertiRespuesta
 *
 * @ORM\Table(name="certi_respuesta", indexes={@ORM\Index(name="IDX_98EB1A4531A5801E", columns={"pregunta_id"}), @ORM\Index(name="IDX_98EB1A455BDBF2F", columns={"opcion_id"}), @ORM\Index(name="IDX_98EB1A45DB38439E", columns={"usuario_id"})})
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
     * @var \Link\ComunBundle\Entity\CertiOpcion
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\CertiOpcion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="opcion_id", referencedColumnName="id")
     * })
     */
    private $opcion;

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
