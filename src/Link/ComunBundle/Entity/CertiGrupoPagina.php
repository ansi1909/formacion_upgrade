<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CertiGrupoPagina
 *
 * @ORM\Table(name="certi_grupo_pagina", indexes={@ORM\Index(name="IDX_7CD7695E9C833003", columns={"grupo_id"}), @ORM\Index(name="IDX_7CD7695E57991ECF", columns={"pagina_id"})})
 * @ORM\Entity
 */
class CertiGrupoPagina
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="certi_grupo_pagina_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \Link\ComunBundle\Entity\CertiGrupo
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\CertiGrupo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grupo_id", referencedColumnName="id")
     * })
     */
    private $grupo;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set grupo
     *
     * @param \Link\ComunBundle\Entity\CertiGrupo $grupo
     *
     * @return CertiGrupoPagina
     */
    public function setGrupo(\Link\ComunBundle\Entity\CertiGrupo $grupo = null)
    {
        $this->grupo = $grupo;
    
        return $this;
    }

    /**
     * Get grupo
     *
     * @return \Link\ComunBundle\Entity\CertiGrupo
     */
    public function getGrupo()
    {
        return $this->grupo;
    }

    /**
     * Set pagina
     *
     * @param \Link\ComunBundle\Entity\CertiPagina $pagina
     *
     * @return CertiGrupoPagina
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
}
