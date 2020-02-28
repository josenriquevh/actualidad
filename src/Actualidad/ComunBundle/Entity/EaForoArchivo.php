<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EaForoArchivo
 *
 * @ORM\Table(name="ea_foro_archivo", indexes={@ORM\Index(name="IDX_C8244EC4F5FF53F6", columns={"foro_id"}), @ORM\Index(name="IDX_C8244EC4DB38439E", columns={"usuario_id"})})
 * @ORM\Entity
 */
class EaForoArchivo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ea_foro_archivo_id_seq", allocationSize=1, initialValue=1)
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
     * @ORM\Column(name="archivo", type="string", length=250, nullable=true)
     */
    private $archivo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_creacion", type="datetime", nullable=true)
     */
    private $fechaCreacion;

    /**
     * @var \Actualidad\ComunBundle\Entity\EaForo
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\EaForo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="foro_id", referencedColumnName="id")
     * })
     */
    private $foro;

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
     * @return EaForoArchivo
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
     * Set archivo
     *
     * @param string $archivo
     *
     * @return EaForoArchivo
     */
    public function setArchivo($archivo)
    {
        $this->archivo = $archivo;
    
        return $this;
    }

    /**
     * Get archivo
     *
     * @return string
     */
    public function getArchivo()
    {
        return $this->archivo;
    }

    /**
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     *
     * @return EaForoArchivo
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
     * Set foro
     *
     * @param \Actualidad\ComunBundle\Entity\EaForo $foro
     *
     * @return EaForoArchivo
     */
    public function setForo(\Actualidad\ComunBundle\Entity\EaForo $foro = null)
    {
        $this->foro = $foro;
    
        return $this;
    }

    /**
     * Get foro
     *
     * @return \Actualidad\ComunBundle\Entity\EaForo
     */
    public function getForo()
    {
        return $this->foro;
    }

    /**
     * Set usuario
     *
     * @param \Actualidad\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return EaForoArchivo
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
