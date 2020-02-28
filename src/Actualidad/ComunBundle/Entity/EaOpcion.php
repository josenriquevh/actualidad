<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EaOpcion
 *
 * @ORM\Table(name="ea_opcion", indexes={@ORM\Index(name="IDX_1FAB270BE7DE889A", columns={"prueba_id"}), @ORM\Index(name="IDX_1FAB270BDB38439E", columns={"usuario_id"})})
 * @ORM\Entity
 */
class EaOpcion
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ea_opcion_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="text", nullable=true)
     */
    private $descripcion;

    /**
     * @var string
     *
     * @ORM\Column(name="imagen", type="string", length=500, nullable=true)
     */
    private $imagen;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_creacion", type="datetime", nullable=true)
     */
    private $fechaCreacion;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_modificacion", type="datetime", nullable=true)
     */
    private $fechaModificacion;

    /**
     * @var \Actualidad\ComunBundle\Entity\EaPrueba
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\EaPrueba")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prueba_id", referencedColumnName="id")
     * })
     */
    private $prueba;

    /**
     * @var \Actualidad\ComunBundle\Entity\AdminUsuario
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\AdminUsuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     * })
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
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return EaOpcion
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    
        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set imagen
     *
     * @param string $imagen
     *
     * @return EaOpcion
     */
    public function setImagen($imagen)
    {
        $this->imagen = $imagen;
    
        return $this;
    }

    /**
     * Get imagen
     *
     * @return string
     */
    public function getImagen()
    {
        return $this->imagen;
    }

    /**
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     *
     * @return EaOpcion
     */
    public function setFechaCreacion($fechaCreacion)
    {
        $this->fechaCreacion = $fechaCreacion;
    
        return $this;
    }

    /**
     * Get fechaCreacion
     *
     * @return \DateTime
     */
    public function getFechaCreacion()
    {
        return $this->fechaCreacion;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     *
     * @return EaOpcion
     */
    public function setFechaModificacion($fechaModificacion)
    {
        $this->fechaModificacion = $fechaModificacion;
    
        return $this;
    }

    /**
     * Get fechaModificacion
     *
     * @return \DateTime
     */
    public function getFechaModificacion()
    {
        return $this->fechaModificacion;
    }

    /**
     * Set prueba
     *
     * @param \Actualidad\ComunBundle\Entity\EaPrueba $prueba
     *
     * @return EaOpcion
     */
    public function setPrueba(\Actualidad\ComunBundle\Entity\EaPrueba $prueba = null)
    {
        $this->prueba = $prueba;
    
        return $this;
    }

    /**
     * Get prueba
     *
     * @return \Actualidad\ComunBundle\Entity\EaPrueba
     */
    public function getPrueba()
    {
        return $this->prueba;
    }

    /**
     * Set usuario
     *
     * @param \Actualidad\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return EaOpcion
     */
    public function setUsuario(\Actualidad\ComunBundle\Entity\AdminUsuario $usuario = null)
    {
        $this->usuario = $usuario;
    
        return $this;
    }

    /**
     * Get usuario
     *
     * @return \Actualidad\ComunBundle\Entity\AdminUsuario
     */
    public function getUsuario()
    {
        return $this->usuario;
    }
}
