<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CertiCategoria
 *
 * @ORM\Table(name="certi_categoria")
 * @ORM\Entity
 */
class CertiCategoria
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="certi_categoria_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=20, nullable=true)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="pronombre", type="string", length=20, nullable=true)
     */
    private $pronombre;

    
    /**
     * @var string
     *
     * @ORM\Column(name="bienvenida", type="string", length=20, nullable=true)
     */
    private $bienvenida;


    /**
     * @var string
     *
     * @ORM\Column(name="tarjetas", type="string", length=40, nullable=true)
     */
    private $tarjetas;





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
     * @return CertiCategoria
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    
        return $this;
    }


     /**
     * Set pronombre
     *
     * @param string $pronombre
     *
     * @return CertiCategoria
     */
    public function setPronombre($pronombre)
    {
        $this->pronombre = $pronombre;
    
        return $this;
    }

    /**
     * Set bienvenida
     *
     * @param string $bienvenida
     *
     * @return CertiCategoria
     */
    public function setBienvenida($bienvenida)
    {
        $this->bienvenida = $bienvenida;
    
        return $this;
    }

    /**
     * Set tarjetas
     *
     * @param string $tarjetas
     *
     * @return CertiCategoria
     */
    public function setTarjetas($tarjetas)
    {
        $this->tarjetas = $tarjetas;
    
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
     * Get pronombre
     *
     * @return string
     */
    public function getPronombre()
    {
        return $this->pronombre;
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
     * Get tarjetas
     *
     * @return string
     */
    public function getTarjetas()
    {
        return $this->tarjetas;
    }
    /**
     * @var bool|null
     */
    private $horas;

    /**
     * @var bool|null
     */
    private $contenido;


    /**
     * Set horas.
     *
     * @param bool|null $horas
     *
     * @return CertiCategoria
     */
    public function setHoras($horas = null)
    {
        $this->horas = $horas;
    
        return $this;
    }

    /**
     * Get horas.
     *
     * @return bool|null
     */
    public function getHoras()
    {
        return $this->horas;
    }

    /**
     * Set contenido.
     *
     * @param bool|null $contenido
     *
     * @return CertiCategoria
     */
    public function setContenido($contenido = null)
    {
        $this->contenido = $contenido;
    
        return $this;
    }

    /**
     * Get contenido.
     *
     * @return bool|null
     */
    public function getContenido()
    {
        return $this->contenido;
    }
    /**
     * @var string|null
     */
    private $notas;


    /**
     * Set notas.
     *
     * @param string|null $notas
     *
     * @return CertiCategoria
     */
    public function setNotas($notas = null)
    {
        $this->notas = $notas;
    
        return $this;
    }

    /**
     * Get notas.
     *
     * @return string|null
     */
    public function getNotas()
    {
        return $this->notas;
    }
}
