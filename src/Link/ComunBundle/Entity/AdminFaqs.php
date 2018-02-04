<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminFaqs
 *
 * @ORM\Table(name="admin_faqs", indexes={@ORM\Index(name="IDX_A92D3C05481AEE6", columns={"tipo_pregunta_id"})})
 * @ORM\Entity
 */
class AdminFaqs
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_faqs_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="pregunta", type="string", length=500, nullable=true)
     */
    private $pregunta;

    /**
     * @var string
     *
     * @ORM\Column(name="respuesta", type="string", length=500, nullable=true)
     */
    private $respuesta;

    /**
     * @var \Link\ComunBundle\Entity\AdminTipoPregunta
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\AdminTipoPregunta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_pregunta_id", referencedColumnName="id")
     * })
     */
    private $tipoPregunta;



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
     * Set pregunta
     *
     * @param string $pregunta
     *
     * @return AdminFaqs
     */
    public function setPregunta($pregunta)
    {
        $this->pregunta = $pregunta;
    
        return $this;
    }

    /**
     * Get pregunta
     *
     * @return string
     */
    public function getPregunta()
    {
        return $this->pregunta;
    }

    /**
     * Set respuesta
     *
     * @param string $respuesta
     *
     * @return AdminFaqs
     */
    public function setRespuesta($respuesta)
    {
        $this->respuesta = $respuesta;
    
        return $this;
    }

    /**
     * Get respuesta
     *
     * @return string
     */
    public function getRespuesta()
    {
        return $this->respuesta;
    }

    /**
     * Set tipoPregunta
     *
     * @param \Link\ComunBundle\Entity\AdminTipoPregunta $tipoPregunta
     *
     * @return AdminFaqs
     */
    public function setTipoPregunta(\Link\ComunBundle\Entity\AdminTipoPregunta $tipoPregunta = null)
    {
        $this->tipoPregunta = $tipoPregunta;
    
        return $this;
    }

    /**
     * Get tipoPregunta
     *
     * @return \Link\ComunBundle\Entity\AdminTipoPregunta
     */
    public function getTipoPregunta()
    {
        return $this->tipoPregunta;
    }
}
