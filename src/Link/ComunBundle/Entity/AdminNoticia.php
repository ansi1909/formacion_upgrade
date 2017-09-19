<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminNoticia
 *
 * @ORM\Table(name="admin_noticia", indexes={@ORM\Index(name="noticia_ndx1", columns={"empresa_id"}), @ORM\Index(name="IDX_F51CDD1FDB38439E", columns={"usuario_id"})})
 * @ORM\Entity
 */
class AdminNoticia
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_noticia_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="tipo", type="string", length=20, nullable=true)
     */
    private $tipo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_registro", type="datetime", nullable=true)
     */
    private $fechaRegistro;

    /**
     * @var string
     *
     * @ORM\Column(name="resumen", type="text", nullable=true)
     */
    private $resumen;

    /**
     * @var string
     *
     * @ORM\Column(name="contenido", type="text", nullable=true)
     */
    private $contenido;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_publicacion", type="date", nullable=true)
     */
    private $fechaPublicacion;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_vencimiento", type="date", nullable=true)
     */
    private $fechaVencimiento;

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
     * Set tipo
     *
     * @param string $tipo
     *
     * @return AdminNoticia
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return string
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     *
     * @return AdminNoticia
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
     * Set resumen
     *
     * @param string $resumen
     *
     * @return AdminNoticia
     */
    public function setResumen($resumen)
    {
        $this->resumen = $resumen;

        return $this;
    }

    /**
     * Get resumen
     *
     * @return string
     */
    public function getResumen()
    {
        return $this->resumen;
    }

    /**
     * Set contenido
     *
     * @param string $contenido
     *
     * @return AdminNoticia
     */
    public function setContenido($contenido)
    {
        $this->contenido = $contenido;

        return $this;
    }

    /**
     * Get contenido
     *
     * @return string
     */
    public function getContenido()
    {
        return $this->contenido;
    }

    /**
     * Set fechaPublicacion
     *
     * @param \DateTime $fechaPublicacion
     *
     * @return AdminNoticia
     */
    public function setFechaPublicacion($fechaPublicacion)
    {
        $this->fechaPublicacion = $fechaPublicacion;

        return $this;
    }

    /**
     * Get fechaPublicacion
     *
     * @return \DateTime
     */
    public function getFechaPublicacion()
    {
        return $this->fechaPublicacion;
    }

    /**
     * Set fechaVencimiento
     *
     * @param \DateTime $fechaVencimiento
     *
     * @return AdminNoticia
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
     * Set empresa
     *
     * @param \Link\ComunBundle\Entity\AdminEmpresa $empresa
     *
     * @return AdminNoticia
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
     * Set usuario
     *
     * @param \Link\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return AdminNoticia
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
