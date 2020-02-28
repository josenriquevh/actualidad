<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminCiudad
 *
 * @ORM\Table(name="admin_ciudad", indexes={@ORM\Index(name="IDX_D34E34674E7121AF", columns={"provincia_id"})})
 * @ORM\Entity
 */
class AdminCiudad
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_ciudad_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=100, nullable=true)
     */
    private $nombre;

    /**
     * @var \Actualidad\ComunBundle\Entity\AdminProvincia
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\AdminProvincia")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="provincia_id", referencedColumnName="id")
     * })
     */
    private $provincia;



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
     * @return AdminCiudad
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
     * Set provincia
     *
     * @param \Actualidad\ComunBundle\Entity\AdminProvincia $provincia
     *
     * @return AdminCiudad
     */
    public function setProvincia(\Actualidad\ComunBundle\Entity\AdminProvincia $provincia = null)
    {
        $this->provincia = $provincia;
    
        return $this;
    }

    /**
     * Get provincia
     *
     * @return \Actualidad\ComunBundle\Entity\AdminProvincia
     */
    public function getProvincia()
    {
        return $this->provincia;
    }
}
