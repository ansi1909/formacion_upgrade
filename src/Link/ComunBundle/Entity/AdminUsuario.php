<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminUsuario
 *
 * @ORM\Table(name="admin_usuario", indexes={@ORM\Index(name="IDX_E65932D4521E1991", columns={"empresa_id"}), @ORM\Index(name="IDX_E65932D4DA3426AE", columns={"nivel_id"}), @ORM\Index(name="IDX_E65932D4C604D5C6", columns={"pais_id"})})
 * @ORM\Entity
 */
class AdminUsuario
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_usuario_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="login", type="string", length=50, nullable=true)
     */
    private $login;

    /**
     * @var string
     *
     * @ORM\Column(name="clave", type="string", length=50, nullable=true)
     */
    private $clave;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=50, nullable=true)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="apellido", type="string", length=50, nullable=true)
     */
    private $apellido;

    /**
     * @var string
     *
     * @ORM\Column(name="correo_personal", type="string", length=100, nullable=true)
     */
    private $correoPersonal;

    /**
     * @var string
     *
     * @ORM\Column(name="correo_corporativo", type="string", length=100, nullable=true)
     */
    private $correoCorporativo;

    /**
     * @var boolean
     *
     * @ORM\Column(name="activo", type="boolean", nullable=true)
     */
    private $activo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_registro", type="datetime", nullable=true)
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_nacimiento", type="date", nullable=true)
     */
    private $fechaNacimiento;

    /**
     * @var string
     *
     * @ORM\Column(name="ciudad", type="string", length=50, nullable=true)
     */
    private $ciudad;

    /**
     * @var string
     *
     * @ORM\Column(name="region", type="string", length=50, nullable=true)
     */
    private $region;

    /**
     * @var string
     *
     * @ORM\Column(name="foto", type="string", length=250, nullable=true)
     */
    private $foto;

    /**
     * @var string
     *
     * @ORM\Column(name="division_funcional", type="string", length=100, nullable=true)
     */
    private $divisionFuncional;

    /**
     * @var string
     *
     * @ORM\Column(name="cargo", type="string", length=100, nullable=true)
     */
    private $cargo;

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
     * @var \Link\ComunBundle\Entity\AdminNivel
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\AdminNivel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="nivel_id", referencedColumnName="id")
     * })
     */
    private $nivel;

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
     * Set login
     *
     * @param string $login
     *
     * @return AdminUsuario
     */
    public function setLogin($login)
    {
        $this->login = $login;
    
        return $this;
    }

    /**
     * Get login
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set clave
     *
     * @param string $clave
     *
     * @return AdminUsuario
     */
    public function setClave($clave)
    {
        $this->clave = $clave;
    
        return $this;
    }

    /**
     * Get clave
     *
     * @return string
     */
    public function getClave()
    {
        return $this->clave;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     *
     * @return AdminUsuario
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
     * Set apellido
     *
     * @param string $apellido
     *
     * @return AdminUsuario
     */
    public function setApellido($apellido)
    {
        $this->apellido = $apellido;
    
        return $this;
    }

    /**
     * Get apellido
     *
     * @return string
     */
    public function getApellido()
    {
        return $this->apellido;
    }

    /**
     * Set correoPersonal
     *
     * @param string $correoPersonal
     *
     * @return AdminUsuario
     */
    public function setCorreoPersonal($correoPersonal)
    {
        $this->correoPersonal = $correoPersonal;
    
        return $this;
    }

    /**
     * Get correoPersonal
     *
     * @return string
     */
    public function getCorreoPersonal()
    {
        return $this->correoPersonal;
    }

    /**
     * Set correoCorporativo
     *
     * @param string $correoCorporativo
     *
     * @return AdminUsuario
     */
    public function setCorreoCorporativo($correoCorporativo)
    {
        $this->correoCorporativo = $correoCorporativo;
    
        return $this;
    }

    /**
     * Get correoCorporativo
     *
     * @return string
     */
    public function getCorreoCorporativo()
    {
        return $this->correoCorporativo;
    }

    /**
     * Set activo
     *
     * @param boolean $activo
     *
     * @return AdminUsuario
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     *
     * @return AdminUsuario
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
     * Set fechaNacimiento
     *
     * @param \DateTime $fechaNacimiento
     *
     * @return AdminUsuario
     */
    public function setFechaNacimiento($fechaNacimiento)
    {
        $this->fechaNacimiento = $fechaNacimiento;
    
        return $this;
    }

    /**
     * Get fechaNacimiento
     *
     * @return \DateTime
     */
    public function getFechaNacimiento()
    {
        return $this->fechaNacimiento;
    }

    /**
     * Set ciudad
     *
     * @param string $ciudad
     *
     * @return AdminUsuario
     */
    public function setCiudad($ciudad)
    {
        $this->ciudad = $ciudad;
    
        return $this;
    }

    /**
     * Get ciudad
     *
     * @return string
     */
    public function getCiudad()
    {
        return $this->ciudad;
    }

    /**
     * Set region
     *
     * @param string $region
     *
     * @return AdminUsuario
     */
    public function setRegion($region)
    {
        $this->region = $region;
    
        return $this;
    }

    /**
     * Get region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set foto
     *
     * @param string $foto
     *
     * @return AdminUsuario
     */
    public function setFoto($foto)
    {
        $this->foto = $foto;
    
        return $this;
    }

    /**
     * Get foto
     *
     * @return string
     */
    public function getFoto()
    {
        return $this->foto;
    }

    /**
     * Set divisionFuncional
     *
     * @param string $divisionFuncional
     *
     * @return AdminUsuario
     */
    public function setDivisionFuncional($divisionFuncional)
    {
        $this->divisionFuncional = $divisionFuncional;
    
        return $this;
    }

    /**
     * Get divisionFuncional
     *
     * @return string
     */
    public function getDivisionFuncional()
    {
        return $this->divisionFuncional;
    }

    /**
     * Set cargo
     *
     * @param string $cargo
     *
     * @return AdminUsuario
     */
    public function setCargo($cargo)
    {
        $this->cargo = $cargo;
    
        return $this;
    }

    /**
     * Get cargo
     *
     * @return string
     */
    public function getCargo()
    {
        return $this->cargo;
    }

    /**
     * Set empresa
     *
     * @param \Link\ComunBundle\Entity\AdminEmpresa $empresa
     *
     * @return AdminUsuario
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
     * Set nivel
     *
     * @param \Link\ComunBundle\Entity\AdminNivel $nivel
     *
     * @return AdminUsuario
     */
    public function setNivel(\Link\ComunBundle\Entity\AdminNivel $nivel = null)
    {
        $this->nivel = $nivel;
    
        return $this;
    }

    /**
     * Get nivel
     *
     * @return \Link\ComunBundle\Entity\AdminNivel
     */
    public function getNivel()
    {
        return $this->nivel;
    }

    /**
     * Set pais
     *
     * @param \Link\ComunBundle\Entity\AdminPais $pais
     *
     * @return AdminUsuario
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
