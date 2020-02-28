<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EaPrueba
 *
 * @ORM\Table(name="ea_prueba", indexes={@ORM\Index(name="IDX_CE9256DEDB38439E", columns={"usuario_id"}), @ORM\Index(name="IDX_CE9256DE57991ECF", columns={"pagina_id"}), @ORM\Index(name="IDX_CE9256DE64373B63", columns={"estatus_contenido_id"})})
 * @ORM\Entity
 */
class EaPrueba
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ea_prueba_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=350, nullable=true)
     */
    private $nombre;

    /**
     * @var integer
     *
     * @ORM\Column(name="cantidad_preguntas", type="integer", nullable=true)
     */
    private $cantidadPreguntas;

    /**
     * @var integer
     *
     * @ORM\Column(name="cantidad_mostrar", type="integer", nullable=true)
     */
    private $cantidadMostrar;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="duracion", type="time", nullable=true)
     */
    private $duracion;

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
     * @var integer
     *
     * @ORM\Column(name="min_correctas", type="integer", nullable=true)
     */
    private $minCorrectas;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_intentos", type="integer", nullable=true)
     */
    private $maxIntentos;

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
     * @var \Actualidad\ComunBundle\Entity\EaPagina
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\EaPagina")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pagina_id", referencedColumnName="id")
     * })
     */
    private $pagina;

    /**
     * @var \Actualidad\ComunBundle\Entity\EaEstatusContenido
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\EaEstatusContenido")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="estatus_contenido_id", referencedColumnName="id")
     * })
     */
    private $estatusContenido;



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
     * @return EaPrueba
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
     * Set cantidadPreguntas
     *
     * @param integer $cantidadPreguntas
     *
     * @return EaPrueba
     */
    public function setCantidadPreguntas($cantidadPreguntas)
    {
        $this->cantidadPreguntas = $cantidadPreguntas;
    
        return $this;
    }

    /**
     * Get cantidadPreguntas
     *
     * @return integer
     */
    public function getCantidadPreguntas()
    {
        return $this->cantidadPreguntas;
    }

    /**
     * Set cantidadMostrar
     *
     * @param integer $cantidadMostrar
     *
     * @return EaPrueba
     */
    public function setCantidadMostrar($cantidadMostrar)
    {
        $this->cantidadMostrar = $cantidadMostrar;
    
        return $this;
    }

    /**
     * Get cantidadMostrar
     *
     * @return integer
     */
    public function getCantidadMostrar()
    {
        return $this->cantidadMostrar;
    }

    /**
     * Set duracion
     *
     * @param \DateTime $duracion
     *
     * @return EaPrueba
     */
    public function setDuracion($duracion)
    {
        $this->duracion = $duracion;
    
        return $this;
    }

    /**
     * Get duracion
     *
     * @return \DateTime
     */
    public function getDuracion()
    {
        return $this->duracion;
    }

    /**
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     *
     * @return EaPrueba
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
     * @return EaPrueba
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
     * Set minCorrectas
     *
     * @param integer $minCorrectas
     *
     * @return EaPrueba
     */
    public function setMinCorrectas($minCorrectas)
    {
        $this->minCorrectas = $minCorrectas;
    
        return $this;
    }

    /**
     * Get minCorrectas
     *
     * @return integer
     */
    public function getMinCorrectas()
    {
        return $this->minCorrectas;
    }

    /**
     * Set maxIntentos
     *
     * @param integer $maxIntentos
     *
     * @return EaPrueba
     */
    public function setMaxIntentos($maxIntentos)
    {
        $this->maxIntentos = $maxIntentos;
    
        return $this;
    }

    /**
     * Get maxIntentos
     *
     * @return integer
     */
    public function getMaxIntentos()
    {
        return $this->maxIntentos;
    }

    /**
     * Set usuario
     *
     * @param \Actualidad\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return EaPrueba
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

    /**
     * Set pagina
     *
     * @param \Actualidad\ComunBundle\Entity\EaPagina $pagina
     *
     * @return EaPrueba
     */
    public function setPagina(\Actualidad\ComunBundle\Entity\EaPagina $pagina = null)
    {
        $this->pagina = $pagina;
    
        return $this;
    }

    /**
     * Get pagina
     *
     * @return \Actualidad\ComunBundle\Entity\EaPagina
     */
    public function getPagina()
    {
        return $this->pagina;
    }

    /**
     * Set estatusContenido
     *
     * @param \Actualidad\ComunBundle\Entity\EaEstatusContenido $estatusContenido
     *
     * @return EaPrueba
     */
    public function setEstatusContenido(\Actualidad\ComunBundle\Entity\EaEstatusContenido $estatusContenido = null)
    {
        $this->estatusContenido = $estatusContenido;
    
        return $this;
    }

    /**
     * Get estatusContenido
     *
     * @return \Actualidad\ComunBundle\Entity\EaEstatusContenido
     */
    public function getEstatusContenido()
    {
        return $this->estatusContenido;
    }
}
