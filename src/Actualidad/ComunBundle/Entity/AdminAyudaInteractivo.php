<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminAyudaInteractivo
 *
 * @ORM\Table(name="admin_ayuda_interactivo")
 * @ORM\Entity
 */
class AdminAyudaInteractivo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_ayuda_interactivo_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="mensaje", type="string", length=500, nullable=true)
     */
    private $mensaje;

    /**
     * @var string
     *
     * @ORM\Column(name="gif", type="string", length=500, nullable=true)
     */
    private $gif;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=500, nullable=true)
     */
    private $nombre;



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
     * Set mensaje
     *
     * @param string $mensaje
     *
     * @return AdminAyudaInteractivo
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
     * Set gif
     *
     * @param string $gif
     *
     * @return AdminAyudaInteractivo
     */
    public function setGif($gif)
    {
        $this->gif = $gif;
    
        return $this;
    }

    /**
     * Get gif
     *
     * @return string
     */
    public function getGif()
    {
        return $this->gif;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     *
     * @return AdminAyudaInteractivo
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
}
