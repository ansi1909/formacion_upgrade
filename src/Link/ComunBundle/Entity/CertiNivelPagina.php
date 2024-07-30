<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CertiNivelPagina
 *
 * @ORM\Table(name="certi_nivel_pagina", indexes={@ORM\Index(name="nivel_pagina_ndx1", columns={"nivel_id", "pagina_empresa_id"}), @ORM\Index(name="IDX_EF46AB9EDA3426AE", columns={"nivel_id"}), @ORM\Index(name="IDX_EF46AB9EDCF6B7C4", columns={"pagina_empresa_id"})})
 * @ORM\Entity
 */
class CertiNivelPagina
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="certi_nivel_pagina_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

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
     * @var \Link\ComunBundle\Entity\CertiPaginaEmpresa
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\CertiPaginaEmpresa")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pagina_empresa_id", referencedColumnName="id")
     * })
     */
    private $paginaEmpresa;



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
     * Set nivel
     *
     * @param \Link\ComunBundle\Entity\AdminNivel $nivel
     *
     * @return CertiNivelPagina
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
     * Set paginaEmpresa
     *
     * @param \Link\ComunBundle\Entity\CertiPaginaEmpresa $paginaEmpresa
     *
     * @return CertiNivelPagina
     */
    public function setPaginaEmpresa(\Link\ComunBundle\Entity\CertiPaginaEmpresa $paginaEmpresa = null)
    {
        $this->paginaEmpresa = $paginaEmpresa;
    
        return $this;
    }

    /**
     * Get paginaEmpresa
     *
     * @return \Link\ComunBundle\Entity\CertiPaginaEmpresa
     */
    public function getPaginaEmpresa()
    {
        return $this->paginaEmpresa;
    }
}
