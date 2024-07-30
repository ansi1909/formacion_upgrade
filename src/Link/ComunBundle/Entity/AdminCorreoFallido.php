<?php

namespace Link\ComunBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * AdminCorreoFallido
 */
class AdminCorreoFallido
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $correo;

    /**
     * @var integer
     */
    private $entidadId;

    /**
     * @var \DateTime
     */
    private $fecha;

    /**
     * @var boolean
     */
    private $reenviado;

    /**
     * @var string
     */
    private $mensaje;

    /**
     * @var \Link\ComunBundle\Entity\AdminUsuario
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
     * Set correo
     *
     * @param string $correo
     *
     * @return AdminCorreoFallido
     */
    public function setCorreo($correo)
    {
        $this->correo = $correo;
    
        return $this;
    }

    /**
     * Get correo
     *
     * @return string
     */
    public function getCorreo()
    {
        return $this->correo;
    }

    /**
     * Set entidadId
     *
     * @param integer $entidadId
     *
     * @return AdminCorreoFallido
     */
    public function setEntidadId($entidadId)
    {
        $this->entidadId = $entidadId;
    
        return $this;
    }

    /**
     * Get entidadId
     *
     * @return integer
     */
    public function getEntidadId()
    {
        return $this->entidadId;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     *
     * @return AdminCorreoFallido
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    
        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set reenviado
     *
     * @param boolean $reenviado
     *
     * @return AdminCorreoFallido
     */
    public function setReenviado($reenviado)
    {
        $this->reenviado = $reenviado;
    
        return $this;
    }

    /**
     * Get reenviado
     *
     * @return boolean
     */
    public function getReenviado()
    {
        return $this->reenviado;
    }

    /**
     * Set mensaje
     *
     * @param string $mensaje
     *
     * @return AdminCorreoFallido
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
     * Set usuario
     *
     * @param \Link\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return AdminCorreoFallido
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

