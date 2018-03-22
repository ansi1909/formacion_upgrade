<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CertiMuro
 *
 * @ORM\Table(name="certi_muro", indexes={@ORM\Index(name="muro_ndx1", columns={"pagina_id"}), @ORM\Index(name="IDX_F736956BDB38439E", columns={"usuario_id"}), @ORM\Index(name="IDX_F736956B55B526F1", columns={"muro_id"}), @ORM\Index(name="IDX_F736956B521E1991", columns={"empresa_id"})})
 * @ORM\Entity
 */
class CertiMuro
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="certi_muro_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="mensaje", type="string", length=350, nullable=true)
     */
    private $mensaje;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_registro", type="datetime", nullable=true)
     */
    private $fechaRegistro;

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
     * @var \Link\ComunBundle\Entity\CertiMuro
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\CertiMuro")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="muro_id", referencedColumnName="id")
     * })
     */
    private $muro;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set mensaje
     *
     * @param string $mensaje
     *
     * @return CertiMuro
     */
    public function setMensaje($mensaje)
    {
        $this->mensaje = $mensaje;
    
        return $this;
    }

    /**
     * Get mensaje
     *
     * @return string
     */
    public function getMensaje()
    {
        return $this->mensaje;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     *
     * @return CertiMuro
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
     * Set pagina
     *
     * @param \Link\ComunBundle\Entity\CertiPagina $pagina
     *
     * @return CertiMuro
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
     * @return CertiMuro
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
     * Set muro
     *
     * @param \Link\ComunBundle\Entity\CertiMuro $muro
     *
     * @return CertiMuro
     */
    public function setMuro(\Link\ComunBundle\Entity\CertiMuro $muro = null)
    {
        $this->muro = $muro;
    
        return $this;
    }

    /**
     * Get muro
     *
     * @return \Link\ComunBundle\Entity\CertiMuro
     */
    public function getMuro()
    {
        return $this->muro;
    }

    /**
     * Set empresa
     *
     * @param \Link\ComunBundle\Entity\AdminEmpresa $empresa
     *
     * @return CertiMuro
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
}
