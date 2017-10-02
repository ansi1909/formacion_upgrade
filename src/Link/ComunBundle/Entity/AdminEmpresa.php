<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminEmpresa
 *
 * @ORM\Table(name="admin_empresa", indexes={@ORM\Index(name="IDX_7CEBD8D9C604D5C6", columns={"pais_id"})})
 * @ORM\Entity
 */
class AdminEmpresa
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_empresa_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=100, nullable=true)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="rif", type="string", length=20, nullable=true)
     */
    private $rif;

    /**
     * @var string
     *
     * @ORM\Column(name="correo_principal", type="string", length=100, nullable=true)
     */
    private $correoPrincipal;

    /**
     * @var boolean
     *
     * @ORM\Column(name="activo", type="boolean", nullable=true)
     */
    private $activo;

    /**
     * @var string
     *
     * @ORM\Column(name="telefono_principal", type="string", length=20, nullable=true)
     */
    private $telefonoPrincipal;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_creacion", type="datetime", nullable=true)
     */
    private $fechaCreacion;

    /**
     * @var string
     *
     * @ORM\Column(name="direccion", type="text", nullable=true)
     */
    private $direccion;

    /**
     * @var string
     *
     * @ORM\Column(name="bienvenida", type="text", nullable=true)
     */
    private $bienvenida;

    /**
     * @var \Link\ComunBundle\Entity\AdminPais
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\AdminPais")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pais_id", referencedColumnName="id")
     * })
     */
    private $pais;



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
     * Set nombre
     *
     * @param string $nombre
     *
     * @return AdminEmpresa
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set rif
     *
     * @param string $rif
     *
     * @return AdminEmpresa
     */
    public function setRif($rif)
    {
        $this->rif = $rif;

        return $this;
    }

    /**
     * Get rif
     *
     * @return string
     */
    public function getRif()
    {
        return $this->rif;
    }

    /**
     * Set correoPrincipal
     *
     * @param string $correoPrincipal
     *
     * @return AdminEmpresa
     */
    public function setCorreoPrincipal($correoPrincipal)
    {
        $this->correoPrincipal = $correoPrincipal;

        return $this;
    }

    /**
     * Get correoPrincipal
     *
     * @return string
     */
    public function getCorreoPrincipal()
    {
        return $this->correoPrincipal;
    }

    /**
     * Set activo
     *
     * @param boolean $activo
     *
     * @return AdminEmpresa
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;

        return $this;
    }

    /**
     * Get activo
     *
     * @return boolean
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * Set telefonoPrincipal
     *
     * @param string $telefonoPrincipal
     *
     * @return AdminEmpresa
     */
    public function setTelefonoPrincipal($telefonoPrincipal)
    {
        $this->telefonoPrincipal = $telefonoPrincipal;

        return $this;
    }

    /**
     * Get telefonoPrincipal
     *
     * @return string
     */
    public function getTelefonoPrincipal()
    {
        return $this->telefonoPrincipal;
    }

    /**
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     *
     * @return AdminEmpresa
     */
    public function setFechaCreacion($fechaCreacion)
    {
        $this->fechaCreacion = $fechaCreacion;

        return $this;
    }

    /**
     * Get fechaCreacion
     *
     * @return \DateTime
     */
    public function getFechaCreacion()
    {
        return $this->fechaCreacion;
    }

    /**
     * Set direccion
     *
     * @param string $direccion
     *
     * @return AdminEmpresa
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;

        return $this;
    }

    /**
     * Get direccion
     *
     * @return string
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * Set bienvenida
     *
     * @param string $bienvenida
     *
     * @return AdminEmpresa
     */
    public function setBienvenida($bienvenida)
    {
        $this->bienvenida = $bienvenida;

        return $this;
    }

    /**
     * Get bienvenida
     *
     * @return string
     */
    public function getBienvenida()
    {
        return $this->bienvenida;
    }

    /**
     * Set pais
     *
     * @param \Link\ComunBundle\Entity\AdminPais $pais
     *
     * @return AdminEmpresa
     */
    public function setPais(\Link\ComunBundle\Entity\AdminPais $pais = null)
    {
        $this->pais = $pais;

        return $this;
    }

    /**
     * Get pais
     *
     * @return \Link\ComunBundle\Entity\AdminPais
     */
    public function getPais()
    {
        return $this->pais;
    }
}
