<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EaPaginaLiberada
 *
 * @ORM\Table(name="ea_pagina_liberada", indexes={@ORM\Index(name="IDX_A8C0EB9157991ECF", columns={"pagina_id"}), @ORM\Index(name="IDX_A8C0EB917A5A413A", columns={"seccion_id"}), @ORM\Index(name="IDX_A8C0EB91DB38439E", columns={"usuario_id"})})
 * @ORM\Entity
 */
class EaPaginaLiberada
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ea_pagina_liberada_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_vencimiento", type="date", nullable=true)
     */
    private $fechaVencimiento;

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
     * @var \Actualidad\ComunBundle\Entity\AdminSeccion
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\AdminSeccion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="seccion_id", referencedColumnName="id")
     * })
     */
    private $seccion;

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
     * Set fechaVencimiento
     *
     * @param \DateTime $fechaVencimiento
     *
     * @return EaPaginaLiberada
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
     * Set pagina
     *
     * @param \Actualidad\ComunBundle\Entity\EaPagina $pagina
     *
     * @return EaPaginaLiberada
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
     * Set seccion
     *
     * @param \Actualidad\ComunBundle\Entity\AdminSeccion $seccion
     *
     * @return EaPaginaLiberada
     */
    public function setSeccion(\Actualidad\ComunBundle\Entity\AdminSeccion $seccion = null)
    {
        $this->seccion = $seccion;
    
        return $this;
    }

    /**
     * Get seccion
     *
     * @return \Actualidad\ComunBundle\Entity\AdminSeccion
     */
    public function getSeccion()
    {
        return $this->seccion;
    }

    /**
     * Set usuario
     *
     * @param \Actualidad\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return EaPaginaLiberada
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
