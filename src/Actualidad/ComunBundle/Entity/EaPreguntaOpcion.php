<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EaPreguntaOpcion
 *
 * @ORM\Table(name="ea_pregunta_opcion", indexes={@ORM\Index(name="IDX_96D3BB8231A5801E", columns={"pregunta_id"}), @ORM\Index(name="IDX_96D3BB825BDBF2F", columns={"opcion_id"})})
 * @ORM\Entity
 */
class EaPreguntaOpcion
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ea_pregunta_opcion_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var boolean
     *
     * @ORM\Column(name="correcta", type="boolean", nullable=true)
     */
    private $correcta;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set correcta
     *
     * @param boolean $correcta
     *
     * @return EaPreguntaOpcion
     */
    public function setCorrecta($correcta)
    {
        $this->correcta = $correcta;
    
        return $this;
    }

    /**
     * Get correcta
     *
     * @return boolean
     */
    public function getCorrecta()
    {
        return $this->correcta;
    }

    /**
     * Set pregunta
     *
     * @param \Actualidad\ComunBundle\Entity\EaPregunta $pregunta
     *
     * @return EaPreguntaOpcion
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
     * @return EaPreguntaOpcion
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
}
