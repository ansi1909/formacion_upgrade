<?php

namespace Link\ComunBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * AdminIntroduccion
 */
class AdminIntroduccion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $pasoActual;

    /**
     * @var boolean
     */
    private $cancelado;

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
     * Set pasoActual
     *
     * @param integer $pasoActual
     *
     * @return AdminIntroduccion
     */
    public function setPasoActual($pasoActual)
    {
        $this->pasoActual = $pasoActual;
    
        return $this;
    }

    /**
     * Get pasoActual
     *
     * @return integer
     */
    public function getPasoActual()
    {
        return $this->pasoActual;
    }

    /**
     * Set cancelado
     *
     * @param boolean $cancelado
     *
     * @return AdminIntroduccion
     */
    public function setCancelado($cancelado)
    {
        $this->cancelado = $cancelado;
    
        return $this;
    }

    /**
     * Get cancelado
     *
     * @return boolean
     */
    public function getCancelado()
    {
        return $this->cancelado;
    }

    /**
     * Set usuario
     *
     * @param \Link\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return AdminIntroduccion
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

