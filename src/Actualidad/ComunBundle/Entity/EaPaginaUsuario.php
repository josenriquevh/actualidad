<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EaPaginaUsuario
 *
 * @ORM\Table(name="ea_pagina_usuario", indexes={@ORM\Index(name="IDX_FAFCC34F57991ECF", columns={"pagina_id"}), @ORM\Index(name="IDX_FAFCC34FDB38439E", columns={"usuario_id"})})
 * @ORM\Entity
 */
class EaPaginaUsuario
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ea_pagina_usuario_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="codigo", type="string", length=20, nullable=true)
     */
    private $codigo;

    /**
     * @var boolean
     *
     * @ORM\Column(name="activo", type="boolean", nullable=true)
     */
    private $activo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_activacion", type="date", nullable=true)
     */
    private $fechaActivacion;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_inicio", type="date", nullable=true)
     */
    private $fechaInicio;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_vencimiento", type="date", nullable=true)
     */
    private $fechaVencimiento;

    /**
     * @var boolean
     *
     * @ORM\Column(name="renovable", type="boolean", nullable=true)
     */
    private $renovable;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_renovaciones", type="integer", nullable=true)
     */
    private $maxRenovaciones;

    /**
     * @var integer
     *
     * @ORM\Column(name="renovaciones", type="integer", nullable=true)
     */
    private $renovaciones;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=20, nullable=true)
     */
    private $token;

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
     * Set codigo
     *
     * @param string $codigo
     *
     * @return EaPaginaUsuario
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    
        return $this;
    }

    /**
     * Get codigo
     *
     * @return string
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set activo
     *
     * @param boolean $activo
     *
     * @return EaPaginaUsuario
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;
    
        return $this;
    }

    /**
     * Get activo
     *
     * @return boolean
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * Set fechaActivacion
     *
     * @param \DateTime $fechaActivacion
     *
     * @return EaPaginaUsuario
     */
    public function setFechaActivacion($fechaActivacion)
    {
        $this->fechaActivacion = $fechaActivacion;
    
        return $this;
    }

    /**
     * Get fechaActivacion
     *
     * @return \DateTime
     */
    public function getFechaActivacion()
    {
        return $this->fechaActivacion;
    }

    /**
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     *
     * @return EaPaginaUsuario
     */
    public function setFechaInicio($fechaInicio)
    {
        $this->fechaInicio = $fechaInicio;
    
        return $this;
    }

    /**
     * Get fechaInicio
     *
     * @return \DateTime
     */
    public function getFechaInicio()
    {
        return $this->fechaInicio;
    }

    /**
     * Set fechaVencimiento
     *
     * @param \DateTime $fechaVencimiento
     *
     * @return EaPaginaUsuario
     */
    public function setFechaVencimiento($fechaVencimiento)
    {
        $this->fechaVencimiento = $fechaVencimiento;
    
        return $this;
    }

    /**
     * Get fechaVencimiento
     *
     * @return \DateTime
     */
    public function getFechaVencimiento()
    {
        return $this->fechaVencimiento;
    }

    /**
     * Set renovable
     *
     * @param boolean $renovable
     *
     * @return EaPaginaUsuario
     */
    public function setRenovable($renovable)
    {
        $this->renovable = $renovable;
    
        return $this;
    }

    /**
     * Get renovable
     *
     * @return boolean
     */
    public function getRenovable()
    {
        return $this->renovable;
    }

    /**
     * Set maxRenovaciones
     *
     * @param integer $maxRenovaciones
     *
     * @return EaPaginaUsuario
     */
    public function setMaxRenovaciones($maxRenovaciones)
    {
        $this->maxRenovaciones = $maxRenovaciones;
    
        return $this;
    }

    /**
     * Get maxRenovaciones
     *
     * @return integer
     */
    public function getMaxRenovaciones()
    {
        return $this->maxRenovaciones;
    }

    /**
     * Set renovaciones
     *
     * @param integer $renovaciones
     *
     * @return EaPaginaUsuario
     */
    public function setRenovaciones($renovaciones)
    {
        $this->renovaciones = $renovaciones;
    
        return $this;
    }

    /**
     * Get renovaciones
     *
     * @return integer
     */
    public function getRenovaciones()
    {
        return $this->renovaciones;
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return EaPaginaUsuario
     */
    public function setToken($token)
    {
        $this->token = $token;
    
        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set pagina
     *
     * @param \Actualidad\ComunBundle\Entity\EaPagina $pagina
     *
     * @return EaPaginaUsuario
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
     * Set usuario
     *
     * @param \Actualidad\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return EaPaginaUsuario
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
