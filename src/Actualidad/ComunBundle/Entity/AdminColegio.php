<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminColegio
 *
 * @ORM\Table(name="admin_colegio", indexes={@ORM\Index(name="IDX_6E3C5AFFE8608214", columns={"ciudad_id"})})
 * @ORM\Entity
 */
class AdminColegio
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_colegio_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=100, nullable=true)
     */
    private $nombre;

    /**
     * @var \Actualidad\ComunBundle\Entity\AdminCiudad
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\AdminCiudad")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ciudad_id", referencedColumnName="id")
     * })
     */
    private $ciudad;



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
     * @return AdminColegio
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
     * Set ciudad
     *
     * @param \Actualidad\ComunBundle\Entity\AdminCiudad $ciudad
     *
     * @return AdminColegio
     */
    public function setCiudad(\Actualidad\ComunBundle\Entity\AdminCiudad $ciudad = null)
    {
        $this->ciudad = $ciudad;
    
        return $this;
    }

    /**
     * Get ciudad
     *
     * @return \Actualidad\ComunBundle\Entity\AdminCiudad
     */
    public function getCiudad()
    {
        return $this->ciudad;
    }
}
