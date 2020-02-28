<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EaRespuesta
 *
 * @ORM\Table(name="ea_respuesta", indexes={@ORM\Index(name="IDX_9806980A31A5801E", columns={"pregunta_id"}), @ORM\Index(name="IDX_9806980A5BDBF2F", columns={"opcion_id"}), @ORM\Index(name="IDX_9806980A25A67894", columns={"prueba_log_id"})})
 * @ORM\Entity
 */
class EaRespuesta
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ea_respuesta_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="nro", type="integer", nullable=true)
     */
    private $nro;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_creacion", type="datetime", nullable=true)
     */
    private $fechaCreacion;

    /**
     * @var \Actualidad\ComunBundle\Entity\EaPregunta
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\EaPregunta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pregunta_id", referencedColumnName="id")
     * })
     */
    private $pregunta;

    /**
     * @var \Actualidad\ComunBundle\Entity\EaOpcion
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\EaOpcion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="opcion_id", referencedColumnName="id")
     * })
     */
    private $opcion;

    /**
     * @var \Actualidad\ComunBundle\Entity\EaPruebaLog
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\EaPruebaLog")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prueba_log_id", referencedColumnName="id")
     * })
     */
    private $pruebaLog;



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
     * Set nro
     *
     * @param integer $nro
     *
     * @return EaRespuesta
     */
    public function setNro($nro)
    {
        $this->nro = $nro;
    
        return $this;
    }

    /**
     * Get nro
     *
     * @return integer
     */
    public function getNro()
    {
        return $this->nro;
    }

    /**
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     *
     * @return EaRespuesta
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
     * Set pregunta
     *
     * @param \Actualidad\ComunBundle\Entity\EaPregunta $pregunta
     *
     * @return EaRespuesta
     */
    public function setPregunta(\Actualidad\ComunBundle\Entity\EaPregunta $pregunta = null)
    {
        $this->pregunta = $pregunta;
    
        return $this;
    }

    /**
     * Get pregunta
     *
     * @return \Actualidad\ComunBundle\Entity\EaPregunta
     */
    public function getPregunta()
    {
        return $this->pregunta;
    }

    /**
     * Set opcion
     *
     * @param \Actualidad\ComunBundle\Entity\EaOpcion $opcion
     *
     * @return EaRespuesta
     */
    public function setOpcion(\Actualidad\ComunBundle\Entity\EaOpcion $opcion = null)
    {
        $this->opcion = $opcion;
    
        return $this;
    }

    /**
     * Get opcion
     *
     * @return \Actualidad\ComunBundle\Entity\EaOpcion
     */
    public function getOpcion()
    {
        return $this->opcion;
    }

    /**
     * Set pruebaLog
     *
     * @param \Actualidad\ComunBundle\Entity\EaPruebaLog $pruebaLog
     *
     * @return EaRespuesta
     */
    public function setPruebaLog(\Actualidad\ComunBundle\Entity\EaPruebaLog $pruebaLog = null)
    {
        $this->pruebaLog = $pruebaLog;
    
        return $this;
    }

    /**
     * Get pruebaLog
     *
     * @return \Actualidad\ComunBundle\Entity\EaPruebaLog
     */
    public function getPruebaLog()
    {
        return $this->pruebaLog;
    }
}
