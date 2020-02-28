<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EaPaginaLog
 *
 * @ORM\Table(name="ea_pagina_log", indexes={@ORM\Index(name="IDX_48F4085557991ECF", columns={"pagina_id"}), @ORM\Index(name="IDX_48F40855DB38439E", columns={"usuario_id"}), @ORM\Index(name="IDX_48F408557D139FE4", columns={"estatus_pagina_id"})})
 * @ORM\Entity
 */
class EaPaginaLog
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ea_pagina_log_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_inicio", type="datetime", nullable=true)
     */
    private $fechaInicio;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_fin", type="datetime", nullable=true)
     */
    private $fechaFin;

    /**
     * @var string
     *
     * @ORM\Column(name="porcentaje_avance", type="decimal", precision=5, scale=2, nullable=true)
     */
    private $porcentajeAvance;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_interaccion", type="datetime", nullable=true)
     */
    private $fechaInteraccion;

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
     * @var \Actualidad\ComunBundle\Entity\EaEstatusPagina
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\EaEstatusPagina")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="estatus_pagina_id", referencedColumnName="id")
     * })
     */
    private $estatusPagina;



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
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     *
     * @return EaPaginaLog
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
     * Set fechaFin
     *
     * @param \DateTime $fechaFin
     *
     * @return EaPaginaLog
     */
    public function setFechaFin($fechaFin)
    {
        $this->fechaFin = $fechaFin;
    
        return $this;
    }

    /**
     * Get fechaFin
     *
     * @return \DateTime
     */
    public function getFechaFin()
    {
        return $this->fechaFin;
    }

    /**
     * Set porcentajeAvance
     *
     * @param string $porcentajeAvance
     *
     * @return EaPaginaLog
     */
    public function setPorcentajeAvance($porcentajeAvance)
    {
        $this->porcentajeAvance = $porcentajeAvance;
    
        return $this;
    }

    /**
     * Get porcentajeAvance
     *
     * @return string
     */
    public function getPorcentajeAvance()
    {
        return $this->porcentajeAvance;
    }

    /**
     * Set fechaInteraccion
     *
     * @param \DateTime $fechaInteraccion
     *
     * @return EaPaginaLog
     */
    public function setFechaInteraccion($fechaInteraccion)
    {
        $this->fechaInteraccion = $fechaInteraccion;
    
        return $this;
    }

    /**
     * Get fechaInteraccion
     *
     * @return \DateTime
     */
    public function getFechaInteraccion()
    {
        return $this->fechaInteraccion;
    }

    /**
     * Set pagina
     *
     * @param \Actualidad\ComunBundle\Entity\EaPagina $pagina
     *
     * @return EaPaginaLog
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
     * @return EaPaginaLog
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
     * Set estatusPagina
     *
     * @param \Actualidad\ComunBundle\Entity\EaEstatusPagina $estatusPagina
     *
     * @return EaPaginaLog
     */
    public function setEstatusPagina(\Actualidad\ComunBundle\Entity\EaEstatusPagina $estatusPagina = null)
    {
        $this->estatusPagina = $estatusPagina;
    
        return $this;
    }

    /**
     * Get estatusPagina
     *
     * @return \Actualidad\ComunBundle\Entity\EaEstatusPagina
     */
    public function getEstatusPagina()
    {
        return $this->estatusPagina;
    }
}
