<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CertiGrupo
 *
 * @ORM\Table(name="certi_grupo", indexes={@ORM\Index(name="grupo_ndx1", columns={"empresa_id"})})
 * @ORM\Entity
 */
class CertiGrupo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="certi_grupo_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=100, nullable=true)
     */
    private $nombre;

    /**
     * @var integer
     *
     * @ORM\Column(name="orden", type="integer", nullable=true)
     */
    private $orden;

    /**
     * @var string
     *
     * @ORM\Column(name="imagen_certificado", type="string", length=250, nullable=true)
     */
    private $imagenCertificado;

    /**
     * @var string
     *
     * @ORM\Column(name="imagen_constancia", type="string", length=250, nullable=true)
     */
    private $imagenConstancia;

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
     * Set nombre
     *
     * @param string $nombre
     *
     * @return CertiGrupo
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
     * Set orden
     *
     * @param integer $orden
     *
     * @return CertiGrupo
     */
    public function setOrden($orden)
    {
        $this->orden = $orden;
    
        return $this;
    }

    /**
     * Get orden
     *
     * @return integer
     */
    public function getOrden()
    {
        return $this->orden;
    }

    /**
     * Set imagenCertificado
     *
     * @param string $imagenCertificado
     *
     * @return CertiGrupo
     */
    public function setImagenCertificado($imagenCertificado)
    {
        $this->imagenCertificado = $imagenCertificado;
    
        return $this;
    }

    /**
     * Get imagenCertificado
     *
     * @return string
     */
    public function getImagenCertificado()
    {
        return $this->imagenCertificado;
    }

    /**
     * Set imagenConstancia
     *
     * @param string $imagenConstancia
     *
     * @return CertiGrupo
     */
    public function setImagenConstancia($imagenConstancia)
    {
        $this->imagenConstancia = $imagenConstancia;
    
        return $this;
    }

    /**
     * Get imagenConstancia
     *
     * @return string
     */
    public function getImagenConstancia()
    {
        return $this->imagenConstancia;
    }

    /**
     * Set empresa
     *
     * @param \Link\ComunBundle\Entity\AdminEmpresa $empresa
     *
     * @return CertiGrupo
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
