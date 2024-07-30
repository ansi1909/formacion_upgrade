<?php

namespace Link\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminThumbnail
 *
 * @ORM\Table(name="admin_thumbnail", indexes={@ORM\Index(name="IDX_2AA023D48C22AA1A", columns={"layout_id"})})
 * @ORM\Entity
 */
class AdminThumbnail
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_thumbnail_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=100, nullable=true)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=250, nullable=true)
     */
    private $url;

    /**
     * @var \Link\ComunBundle\Entity\AdminLayout
     *
     * @ORM\ManyToOne(targetEntity="Link\ComunBundle\Entity\AdminLayout")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="layout_id", referencedColumnName="id")
     * })
     */
    private $layout;



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
     * @return AdminThumbnail
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
     * Set url
     *
     * @param string $url
     *
     * @return AdminThumbnail
     */
    public function setUrl($url)
    {
        $this->url = $url;
    
        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set layout
     *
     * @param \Link\ComunBundle\Entity\AdminLayout $layout
     *
     * @return AdminThumbnail
     */
    public function setLayout(\Link\ComunBundle\Entity\AdminLayout $layout = null)
    {
        $this->layout = $layout;
    
        return $this;
    }

    /**
     * Get layout
     *
     * @return \Link\ComunBundle\Entity\AdminLayout
     */
    public function getLayout()
    {
        return $this->layout;
    }
}
