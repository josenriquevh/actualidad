<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EaPagina
 *
 * @ORM\Table(name="ea_pagina", indexes={@ORM\Index(name="IDX_B66D92F0521E1991", columns={"empresa_id"}), @ORM\Index(name="IDX_B66D92F064373B63", columns={"estatus_contenido_id"}), @ORM\Index(name="IDX_B66D92F043798DA7", columns={"estilo_id"}), @ORM\Index(name="IDX_B66D92F091A441CC", columns={"grado_id"}), @ORM\Index(name="IDX_B66D92F057991ECF", columns={"pagina_id"}), @ORM\Index(name="IDX_B66D92F083AF865A", columns={"pagina_referencia_id"}), @ORM\Index(name="IDX_B66D92F065FD4D63", columns={"prelada"}), @ORM\Index(name="IDX_B66D92F0B5F7A181", columns={"tipo_pagina_id"}), @ORM\Index(name="IDX_B66D92F0DB38439E", columns={"usuario_id"}), @ORM\Index(name="IDX_B66D92F0FE6D734", columns={"ayuda_interactivo_id"})})
 * @ORM\Entity
 */
class EaPagina
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ea_pagina_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="titulo", type="string", length=500, nullable=true)
     */
    private $titulo;

    /**
     * @var string
     *
     * @ORM\Column(name="subtitulo", type="string", length=500, nullable=true)
     */
    private $subtitulo;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="text", nullable=true)
     */
    private $descripcion;

    /**
     * @var string
     *
     * @ORM\Column(name="contenido", type="text", nullable=true)
     */
    private $contenido;

    /**
     * @var string
     *
     * @ORM\Column(name="foto", type="string", length=500, nullable=true)
     */
    private $foto;

    /**
     * @var string
     *
     * @ORM\Column(name="pdf", type="string", length=500, nullable=true)
     */
    private $pdf;

    /**
     * @var integer
     *
     * @ORM\Column(name="orden", type="integer", nullable=true)
     */
    private $orden;

    /**
     * @var boolean
     *
     * @ORM\Column(name="interactivo", type="boolean", nullable=true)
     */
    private $interactivo;

    /**
     * @var string
     *
     * @ORM\Column(name="codigo_interactivo", type="string", length=50, nullable=true)
     */
    private $codigoInteractivo;

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
     * @var \Actualidad\ComunBundle\Entity\AdminEmpresa
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\AdminEmpresa")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="empresa_id", referencedColumnName="id")
     * })
     */
    private $empresa;

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
     * @var \Actualidad\ComunBundle\Entity\AdminEstilo
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\AdminEstilo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="estilo_id", referencedColumnName="id")
     * })
     */
    private $estilo;

    /**
     * @var \Actualidad\ComunBundle\Entity\AdminGrado
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\AdminGrado")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grado_id", referencedColumnName="id")
     * })
     */
    private $grado;

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
     * @var \Actualidad\ComunBundle\Entity\EaPagina
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\EaPagina")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pagina_referencia_id", referencedColumnName="id")
     * })
     */
    private $paginaReferencia;

    /**
     * @var \Actualidad\ComunBundle\Entity\EaPagina
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\EaPagina")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prelada", referencedColumnName="id")
     * })
     */
    private $prelada;

    /**
     * @var \Actualidad\ComunBundle\Entity\EaTipoPagina
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\EaTipoPagina")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_pagina_id", referencedColumnName="id")
     * })
     */
    private $tipoPagina;

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
     * @var \Actualidad\ComunBundle\Entity\AdminAyudaInteractivo
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\AdminAyudaInteractivo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ayuda_interactivo_id", referencedColumnName="id")
     * })
     */
    private $ayudaInteractivo;



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
     * Set titulo
     *
     * @param string $titulo
     *
     * @return EaPagina
     */
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;
    
        return $this;
    }

    /**
     * Get titulo
     *
     * @return string
     */
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * Set subtitulo
     *
     * @param string $subtitulo
     *
     * @return EaPagina
     */
    public function setSubtitulo($subtitulo)
    {
        $this->subtitulo = $subtitulo;
    
        return $this;
    }

    /**
     * Get subtitulo
     *
     * @return string
     */
    public function getSubtitulo()
    {
        return $this->subtitulo;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return EaPagina
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
     * Set contenido
     *
     * @param string $contenido
     *
     * @return EaPagina
     */
    public function setContenido($contenido)
    {
        $this->contenido = $contenido;
    
        return $this;
    }

    /**
     * Get contenido
     *
     * @return string
     */
    public function getContenido()
    {
        return $this->contenido;
    }

    /**
     * Set foto
     *
     * @param string $foto
     *
     * @return EaPagina
     */
    public function setFoto($foto)
    {
        $this->foto = $foto;
    
        return $this;
    }

    /**
     * Get foto
     *
     * @return string
     */
    public function getFoto()
    {
        return $this->foto;
    }

    /**
     * Set pdf
     *
     * @param string $pdf
     *
     * @return EaPagina
     */
    public function setPdf($pdf)
    {
        $this->pdf = $pdf;
    
        return $this;
    }

    /**
     * Get pdf
     *
     * @return string
     */
    public function getPdf()
    {
        return $this->pdf;
    }

    /**
     * Set orden
     *
     * @param integer $orden
     *
     * @return EaPagina
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
     * Set interactivo
     *
     * @param boolean $interactivo
     *
     * @return EaPagina
     */
    public function setInteractivo($interactivo)
    {
        $this->interactivo = $interactivo;
    
        return $this;
    }

    /**
     * Get interactivo
     *
     * @return boolean
     */
    public function getInteractivo()
    {
        return $this->interactivo;
    }

    /**
     * Set codigoInteractivo
     *
     * @param string $codigoInteractivo
     *
     * @return EaPagina
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
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     *
     * @return EaPagina
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
     * @return EaPagina
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
     * Set empresa
     *
     * @param \Actualidad\ComunBundle\Entity\AdminEmpresa $empresa
     *
     * @return EaPagina
     */
    public function setEmpresa(\Actualidad\ComunBundle\Entity\AdminEmpresa $empresa = null)
    {
        $this->empresa = $empresa;
    
        return $this;
    }

    /**
     * Get empresa
     *
     * @return \Actualidad\ComunBundle\Entity\AdminEmpresa
     */
    public function getEmpresa()
    {
        return $this->empresa;
    }

    /**
     * Set estatusContenido
     *
     * @param \Actualidad\ComunBundle\Entity\EaEstatusContenido $estatusContenido
     *
     * @return EaPagina
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

    /**
     * Set estilo
     *
     * @param \Actualidad\ComunBundle\Entity\AdminEstilo $estilo
     *
     * @return EaPagina
     */
    public function setEstilo(\Actualidad\ComunBundle\Entity\AdminEstilo $estilo = null)
    {
        $this->estilo = $estilo;
    
        return $this;
    }

    /**
     * Get estilo
     *
     * @return \Actualidad\ComunBundle\Entity\AdminEstilo
     */
    public function getEstilo()
    {
        return $this->estilo;
    }

    /**
     * Set grado
     *
     * @param \Actualidad\ComunBundle\Entity\AdminGrado $grado
     *
     * @return EaPagina
     */
    public function setGrado(\Actualidad\ComunBundle\Entity\AdminGrado $grado = null)
    {
        $this->grado = $grado;
    
        return $this;
    }

    /**
     * Get grado
     *
     * @return \Actualidad\ComunBundle\Entity\AdminGrado
     */
    public function getGrado()
    {
        return $this->grado;
    }

    /**
     * Set pagina
     *
     * @param \Actualidad\ComunBundle\Entity\EaPagina $pagina
     *
     * @return EaPagina
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
     * Set paginaReferencia
     *
     * @param \Actualidad\ComunBundle\Entity\EaPagina $paginaReferencia
     *
     * @return EaPagina
     */
    public function setPaginaReferencia(\Actualidad\ComunBundle\Entity\EaPagina $paginaReferencia = null)
    {
        $this->paginaReferencia = $paginaReferencia;
    
        return $this;
    }

    /**
     * Get paginaReferencia
     *
     * @return \Actualidad\ComunBundle\Entity\EaPagina
     */
    public function getPaginaReferencia()
    {
        return $this->paginaReferencia;
    }

    /**
     * Set prelada
     *
     * @param \Actualidad\ComunBundle\Entity\EaPagina $prelada
     *
     * @return EaPagina
     */
    public function setPrelada(\Actualidad\ComunBundle\Entity\EaPagina $prelada = null)
    {
        $this->prelada = $prelada;
    
        return $this;
    }

    /**
     * Get prelada
     *
     * @return \Actualidad\ComunBundle\Entity\EaPagina
     */
    public function getPrelada()
    {
        return $this->prelada;
    }

    /**
     * Set tipoPagina
     *
     * @param \Actualidad\ComunBundle\Entity\EaTipoPagina $tipoPagina
     *
     * @return EaPagina
     */
    public function setTipoPagina(\Actualidad\ComunBundle\Entity\EaTipoPagina $tipoPagina = null)
    {
        $this->tipoPagina = $tipoPagina;
    
        return $this;
    }

    /**
     * Get tipoPagina
     *
     * @return \Actualidad\ComunBundle\Entity\EaTipoPagina
     */
    public function getTipoPagina()
    {
        return $this->tipoPagina;
    }

    /**
     * Set usuario
     *
     * @param \Actualidad\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return EaPagina
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
     * Set ayudaInteractivo
     *
     * @param \Actualidad\ComunBundle\Entity\AdminAyudaInteractivo $ayudaInteractivo
     *
     * @return EaPagina
     */
    public function setAyudaInteractivo(\Actualidad\ComunBundle\Entity\AdminAyudaInteractivo $ayudaInteractivo = null)
    {
        $this->ayudaInteractivo = $ayudaInteractivo;
    
        return $this;
    }

    /**
     * Get ayudaInteractivo
     *
     * @return \Actualidad\ComunBundle\Entity\AdminAyudaInteractivo
     */
    public function getAyudaInteractivo()
    {
        return $this->ayudaInteractivo;
    }
}
