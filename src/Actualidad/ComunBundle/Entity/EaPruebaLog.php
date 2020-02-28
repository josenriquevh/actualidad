<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EaPruebaLog
 *
 * @ORM\Table(name="ea_prueba_log", indexes={@ORM\Index(name="IDX_5345EAB8E7DE889A", columns={"prueba_id"}), @ORM\Index(name="IDX_5345EAB8DB38439E", columns={"usuario_id"})})
 * @ORM\Entity
 */
class EaPruebaLog
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ea_prueba_log_id_seq", allocationSize=1, initialValue=1)
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
     * @var integer
     *
     * @ORM\Column(name="correctas", type="integer", nullable=true)
     */
    private $correctas;

    /**
     * @var integer
     *
     * @ORM\Column(name="erradas", type="integer", nullable=true)
     */
    private $erradas;

    /**
     * @var string
     *
     * @ORM\Column(name="nota", type="decimal", precision=5, scale=2, nullable=true)
     */
    private $nota;

    /**
     * @var string
     *
     * @ORM\Column(name="estado", type="string", length=15, nullable=true)
     */
    private $estado;

    /**
     * @var string
     *
     * @ORM\Column(name="preguntas_erradas", type="string", length=100, nullable=true)
     */
    private $preguntasErradas;

    /**
     * @var string
     *
     * @ORM\Column(name="preguntas_correctas", type="string", length=100, nullable=true)
     */
    private $preguntasCorrectas;

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
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     *
     * @return EaPruebaLog
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
     * @return EaPruebaLog
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
     * @return EaPruebaLog
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
     * Set correctas
     *
     * @param integer $correctas
     *
     * @return EaPruebaLog
     */
    public function setCorrectas($correctas)
    {
        $this->correctas = $correctas;
    
        return $this;
    }

    /**
     * Get correctas
     *
     * @return integer
     */
    public function getCorrectas()
    {
        return $this->correctas;
    }

    /**
     * Set erradas
     *
     * @param integer $erradas
     *
     * @return EaPruebaLog
     */
    public function setErradas($erradas)
    {
        $this->erradas = $erradas;
    
        return $this;
    }

    /**
     * Get erradas
     *
     * @return integer
     */
    public function getErradas()
    {
        return $this->erradas;
    }

    /**
     * Set nota
     *
     * @param string $nota
     *
     * @return EaPruebaLog
     */
    public function setNota($nota)
    {
        $this->nota = $nota;
    
        return $this;
    }

    /**
     * Get nota
     *
     * @return string
     */
    public function getNota()
    {
        return $this->nota;
    }

    /**
     * Set estado
     *
     * @param string $estado
     *
     * @return EaPruebaLog
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    
        return $this;
    }

    /**
     * Get estado
     *
     * @return string
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set preguntasErradas
     *
     * @param string $preguntasErradas
     *
     * @return EaPruebaLog
     */
    public function setPreguntasErradas($preguntasErradas)
    {
        $this->preguntasErradas = $preguntasErradas;
    
        return $this;
    }

    /**
     * Get preguntasErradas
     *
     * @return string
     */
    public function getPreguntasErradas()
    {
        return $this->preguntasErradas;
    }

    /**
     * Set preguntasCorrectas
     *
     * @param string $preguntasCorrectas
     *
     * @return EaPruebaLog
     */
    public function setPreguntasCorrectas($preguntasCorrectas)
    {
        $this->preguntasCorrectas = $preguntasCorrectas;
    
        return $this;
    }

    /**
     * Get preguntasCorrectas
     *
     * @return string
     */
    public function getPreguntasCorrectas()
    {
        return $this->preguntasCorrectas;
    }

    /**
     * Set prueba
     *
     * @param \Actualidad\ComunBundle\Entity\EaPrueba $prueba
     *
     * @return EaPruebaLog
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
     * @return EaPruebaLog
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
