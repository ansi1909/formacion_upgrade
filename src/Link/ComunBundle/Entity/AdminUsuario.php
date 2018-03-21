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
     * @ORM\Column(name="campo1", type="string", length=50, nullable=true)
     */
    private $campo1;

    /**
     * @var string
     *
     * @ORM\Column(name="campo2", type="string", length=50, nullable=true)
     */
    private $campo2;

    /**
     * @var string
     *
     * @ORM\Column(name="foto", type="string", length=250, nullable=true)
     */
    private $foto;

    /**
     * @var string
     *
     * @ORM\Column(name="campo3", type="string", length=100, nullable=true)
     */
    private $campo3;

    /**
     * @var string
     *
     * @ORM\Column(name="campo4", type="string", length=100, nullable=true)
     */
    private $campo4;

    /**
     * @var boolean
     *
     * @ORM\Column(name="competencia", type="boolean", nullable=true)
     */
    private $competencia;

    /**
     * @var string
     *
     * @ORM\Column(name="codigo", type="string", length=50, nullable=true)
     */
    private $codigo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_modificacion", type="datetime", nullable=true)
     */
    private $fechaModificacion;

    /**
     * @var string
     *
     * @ORM\Column(name="cookies", type="string", length=100, nullable=true)
     */
    private $cookies;

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
     * Set campo1
     *
     * @param string $campo1
     *
     * @return AdminUsuario
     */
    public function setCampo1($campo1)
    {
        $this->campo1 = $campo1;
    
        return $this;
    }

    /**
     * Get campo1
     *
     * @return string
     */
    public function getCampo1()
    {
        return $this->campo1;
    }

    /**
     * Set campo2
     *
     * @param string $campo2
     *
     * @return AdminUsuario
     */
    public function setCampo2($campo2)
    {
        $this->campo2 = $campo2;
    
        return $this;
    }

    /**
     * Get campo2
     *
     * @return string
     */
    public function getCampo2()
    {
        return $this->campo2;
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
     * Set campo3
     *
     * @param string $campo3
     *
     * @return AdminUsuario
     */
    public function setCampo3($campo3)
    {
        $this->campo3 = $campo3;
    
        return $this;
    }

    /**
     * Get campo3
     *
     * @return string
     */
    public function getCampo3()
    {
        return $this->campo3;
    }

    /**
     * Set campo4
     *
     * @param string $campo4
     *
     * @return AdminUsuario
     */
    public function setCampo4($campo4)
    {
        $this->campo4 = $campo4;
    
        return $this;
    }

    /**
     * Get campo4
     *
     * @return string
     */
    public function getCampo4()
    {
        return $this->campo4;
    }

    /**
     * Set competencia
     *
     * @param boolean $competencia
     *
     * @return AdminUsuario
     */
    public function setCompetencia($competencia)
    {
        $this->competencia = $competencia;
    
        return $this;
    }

    /**
     * Get competencia
     *
     * @return boolean
     */
    public function getCompetencia()
    {
        return $this->competencia;
    }

    /**
     * Set codigo
     *
     * @param string $codigo
     *
     * @return AdminUsuario
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    
        return $this;
    }

    /**
     * Get codigo
     *
     * @return string
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     *
     * @return AdminUsuario
     */
    public function setFechaModificacion($fechaModificacion)
    {
        $this->fechaModificacion = $fechaModificacion;
    
        return $this;
    }

    /**
     * Get fechaModificacion
     *
     * @return \DateTime
     */
    public function getFechaModificacion()
    {
        return $this->fechaModificacion;
    }

    /**
     * Set cookies
     *
     * @param string $cookies
     *
     * @return AdminUsuario
     */
    public function setCookies($cookies)
    {
        $this->cookies = $cookies;
    
        return $this;
    }

    /**
     * Get cookies
     *
     * @return string
     */
    public function getCookies()
    {
        return $this->cookies;
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
