<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EaPreguntaAsociacion
 *
 * @ORM\Table(name="ea_pregunta_asociacion", indexes={@ORM\Index(name="IDX_BA0EB13E31A5801E", columns={"pregunta_id"})})
 * @ORM\Entity
 */
class EaPreguntaAsociacion
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ea_pregunta_asociacion_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="preguntas", type="string", length=50, nullable=true)
     */
    private $preguntas;

    /**
     * @var string
     *
     * @ORM\Column(name="opciones", type="string", length=50, nullable=true)
     */
    private $opciones;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set preguntas
     *
     * @param string $preguntas
     *
     * @return EaPreguntaAsociacion
     */
    public function setPreguntas($preguntas)
    {
        $this->preguntas = $preguntas;
    
        return $this;
    }

    /**
     * Get preguntas
     *
     * @return string
     */
    public function getPreguntas()
    {
        return $this->preguntas;
    }

    /**
     * Set opciones
     *
     * @param string $opciones
     *
     * @return EaPreguntaAsociacion
     */
    public function setOpciones($opciones)
    {
        $this->opciones = $opciones;
    
        return $this;
    }

    /**
     * Get opciones
     *
     * @return string
     */
    public function getOpciones()
    {
        return $this->opciones;
    }

    /**
     * Set pregunta
     *
     * @param \Actualidad\ComunBundle\Entity\EaPregunta $pregunta
     *
     * @return EaPreguntaAsociacion
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
}
