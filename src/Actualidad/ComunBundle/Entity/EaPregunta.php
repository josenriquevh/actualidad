<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EaPregunta
 *
 * @ORM\Table(name="ea_pregunta", indexes={@ORM\Index(name="IDX_120854E1E7DE889A", columns={"prueba_id"}), @ORM\Index(name="IDX_120854E1481AEE6", columns={"tipo_pregunta_id"}), @ORM\Index(name="IDX_120854E1F4868001", columns={"tipo_elemento_id"}), @ORM\Index(name="IDX_120854E131A5801E", columns={"pregunta_id"}), @ORM\Index(name="IDX_120854E1DB38439E", columns={"usuario_id"})})
 * @ORM\Entity
 */
class EaPregunta
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ea_pregunta_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="enunciado", type="string", length=500, nullable=true)
     */
    private $enunciado;

    /**
     * @var string
     *
     * @ORM\Column(name="imagen", type="string", length=500, nullable=true)
     */
    private $imagen;

    /**
     * @var string
     *
     * @ORM\Column(name="valor", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $valor;

    /**
     * @var integer
     *
     * @ORM\Column(name="orden", type="integer", nullable=true)
     */
    private $orden;

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
     * @var string
     *
     * @ORM\Column(name="codigo_interactivo", type="string", length=50, nullable=true)
     */
    private $codigoInteractivo;

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
     * @var \Actualidad\ComunBundle\Entity\EaTipoPregunta
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\EaTipoPregunta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_pregunta_id", referencedColumnName="id")
     * })
     */
    private $tipoPregunta;

    /**
     * @var \Actualidad\ComunBundle\Entity\EaTipoElemento
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\EaTipoElemento")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_elemento_id", referencedColumnName="id")
     * })
     */
    private $tipoElemento;

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
     * Set enunciado
     *
     * @param string $enunciado
     *
     * @return EaPregunta
     */
    public function setEnunciado($enunciado)
    {
        $this->enunciado = $enunciado;
    
        return $this;
    }

    /**
     * Get enunciado
     *
     * @return string
     */
    public function getEnunciado()
    {
        return $this->enunciado;
    }

    /**
     * Set imagen
     *
     * @param string $imagen
     *
     * @return EaPregunta
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
     * Set valor
     *
     * @param string $valor
     *
     * @return EaPregunta
     */
    public function setValor($valor)
    {
        $this->valor = $valor;
    
        return $this;
    }

    /**
     * Get valor
     *
     * @return string
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * Set orden
     *
     * @param integer $orden
     *
     * @return EaPregunta
     */
    public function setOrden($orden)
    {
        $this->orden = $orden;
    
        return $this;
    }

    /**
     * Get orden
     *
     * @return integer
     */
    public function getOrden()
    {
        return $this->orden;
    }

    /**
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     *
     * @return EaPregunta
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
     * @return EaPregunta
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
     * Set codigoInteractivo
     *
     * @param string $codigoInteractivo
     *
     * @return EaPregunta
     */
    public function setCodigoInteractivo($codigoInteractivo)
    {
        $this->codigoInteractivo = $codigoInteractivo;
    
        return $this;
    }

    /**
     * Get codigoInteractivo
     *
     * @return string
     */
    public function getCodigoInteractivo()
    {
        return $this->codigoInteractivo;
    }

    /**
     * Set prueba
     *
     * @param \Actualidad\ComunBundle\Entity\EaPrueba $prueba
     *
     * @return EaPregunta
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
     * Set tipoPregunta
     *
     * @param \Actualidad\ComunBundle\Entity\EaTipoPregunta $tipoPregunta
     *
     * @return EaPregunta
     */
    public function setTipoPregunta(\Actualidad\ComunBundle\Entity\EaTipoPregunta $tipoPregunta = null)
    {
        $this->tipoPregunta = $tipoPregunta;
    
        return $this;
    }

    /**
     * Get tipoPregunta
     *
     * @return \Actualidad\ComunBundle\Entity\EaTipoPregunta
     */
    public function getTipoPregunta()
    {
        return $this->tipoPregunta;
    }

    /**
     * Set tipoElemento
     *
     * @param \Actualidad\ComunBundle\Entity\EaTipoElemento $tipoElemento
     *
     * @return EaPregunta
     */
    public function setTipoElemento(\Actualidad\ComunBundle\Entity\EaTipoElemento $tipoElemento = null)
    {
        $this->tipoElemento = $tipoElemento;
    
        return $this;
    }

    /**
     * Get tipoElemento
     *
     * @return \Actualidad\ComunBundle\Entity\EaTipoElemento
     */
    public function getTipoElemento()
    {
        return $this->tipoElemento;
    }

    /**
     * Set pregunta
     *
     * @param \Actualidad\ComunBundle\Entity\EaPregunta $pregunta
     *
     * @return EaPregunta
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
     * Set usuario
     *
     * @param \Actualidad\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return EaPregunta
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
