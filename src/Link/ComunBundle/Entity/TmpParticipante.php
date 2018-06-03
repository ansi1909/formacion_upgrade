<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TmpParticipante
 *
 * @ORM\Table(name="tmp_participante", indexes={@ORM\Index(name="IDX_6321D189521E1991", columns={"empresa_id"}), @ORM\Index(name="IDX_6321D189DA3426AE", columns={"nivel_id"}), @ORM\Index(name="IDX_6321D189C604D5C6", columns={"pais_id"})})
 * @ORM\Entity
 */
class TmpParticipante
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="tmp_participante_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="codigo", type="string", length=50, nullable=true)
     */
    private $codigo;

    /**
     * @var string
     *
     * @ORM\Column(name="login", type="string", length=50, nullable=true)
     */
    private $login;

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
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_registro", type="date", nullable=true)
     */
    private $fechaRegistro;

    /**
     * @var string
     *
     * @ORM\Column(name="clave", type="string", length=50, nullable=true)
     */
    private $clave;

    /**
     * @var string
     *
     * @ORM\Column(name="correo_personal", type="string", length=100, nullable=true)
     */
    private $correoPersonal;

    /**
     * @var boolean
     *
     * @ORM\Column(name="competencia", type="boolean", nullable=true)
     */
    private $competencia;

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
     * @var string
     *
     * @ORM\Column(name="transaccion", type="string", length=10, nullable=true)
     */
    private $transaccion;

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
     * Set codigo
     *
     * @param string $codigo
     *
     * @return TmpParticipante
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
     * Set login
     *
     * @param string $login
     *
     * @return TmpParticipante
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
     * Set nombre
     *
     * @param string $nombre
     *
     * @return TmpParticipante
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
     * @return TmpParticipante
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     *
     * @return TmpParticipante
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
     * Set clave
     *
     * @param string $clave
     *
     * @return TmpParticipante
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
     * Set correoPersonal
     *
     * @param string $correoPersonal
     *
     * @return TmpParticipante
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
     * Set competencia
     *
     * @param boolean $competencia
     *
     * @return TmpParticipante
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
     * Set campo1
     *
     * @param string $campo1
     *
     * @return TmpParticipante
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
     * @return TmpParticipante
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
     * Set campo3
     *
     * @param string $campo3
     *
     * @return TmpParticipante
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
     * @return TmpParticipante
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
     * Set transaccion
     *
     * @param string $transaccion
     *
     * @return TmpParticipante
     */
    public function setTransaccion($transaccion)
    {
        $this->transaccion = $transaccion;
    
        return $this;
    }

    /**
     * Get transaccion
     *
     * @return string
     */
    public function getTransaccion()
    {
        return $this->transaccion;
    }

    /**
     * Set empresa
     *
     * @param \Link\ComunBundle\Entity\AdminEmpresa $empresa
     *
     * @return TmpParticipante
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
     * @return TmpParticipante
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
     * @return TmpParticipante
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
