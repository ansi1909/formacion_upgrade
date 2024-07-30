<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminMedallasUsuario
 *
 * @ORM\Table(name="admin_medallas_usuario", indexes={@ORM\Index(name="IDX_66DDE05CDB38439E", columns={"usuario_id"}), @ORM\Index(name="IDX_66DDE05C91691364", columns={"medalla_id"}), @ORM\Index(name="IDX_66DDE05C57991ECF", columns={"pagina_id"})})
 * @ORM\Entity
 */
class AdminMedallasUsuario
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_medallas_usuario_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \Link\ComunBundle\Entity\AdminUsuario
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\AdminUsuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     * })
     */
    private $usuario;

    /**
     * @var \Link\ComunBundle\Entity\AdminMedallas
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\AdminMedallas")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="medalla_id", referencedColumnName="id")
     * })
     */
    private $medalla;

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
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set usuario.
     *
     * @param \Link\ComunBundle\Entity\AdminUsuario|null $usuario
     *
     * @return AdminMedallasUsuario
     */
    public function setUsuario(\Link\ComunBundle\Entity\AdminUsuario $usuario = null)
    {
        $this->usuario = $usuario;
    
        return $this;
    }

    /**
     * Get usuario.
     *
     * @return \Link\ComunBundle\Entity\AdminUsuario|null
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set medalla.
     *
     * @param \Link\ComunBundle\Entity\AdminMedallas|null $medalla
     *
     * @return AdminMedallasUsuario
     */
    public function setMedalla(\Link\ComunBundle\Entity\AdminMedallas $medalla = null)
    {
        $this->medalla = $medalla;
    
        return $this;
    }

    /**
     * Get medalla.
     *
     * @return \Link\ComunBundle\Entity\AdminMedallas|null
     */
    public function getMedalla()
    {
        return $this->medalla;
    }

    /**
     * Set pagina.
     *
     * @param \Link\ComunBundle\Entity\CertiPagina|null $pagina
     *
     * @return AdminMedallasUsuario
     */
    public function setPagina(\Link\ComunBundle\Entity\CertiPagina $pagina = null)
    {
        $this->pagina = $pagina;
    
        return $this;
    }

    /**
     * Get pagina.
     *
     * @return \Link\ComunBundle\Entity\CertiPagina|null
     */
    public function getPagina()
    {
        return $this->pagina;
    }
}
