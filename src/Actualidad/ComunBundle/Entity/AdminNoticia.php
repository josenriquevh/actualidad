<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminNoticia
 *
 * @ORM\Table(name="admin_noticia", indexes={@ORM\Index(name="IDX_F51CDD1F8146238", columns={"tipo_noticia_id"}), @ORM\Index(name="IDX_F51CDD1FDB38439E", columns={"usuario_id"})})
 * @ORM\Entity
 */
class AdminNoticia
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_noticia_id_seq", allocationSize=1, initialValue=1)
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
     * @ORM\Column(name="resumen", type="text", nullable=true)
     */
    private $resumen;

    /**
     * @var string
     *
     * @ORM\Column(name="contenido", type="text", nullable=true)
     */
    private $contenido;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_publicacion", type="date", nullable=true)
     */
    private $fechaPublicacion;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_vencimiento", type="date", nullable=true)
     */
    private $fechaVencimiento;

    /**
     * @var string
     *
     * @ORM\Column(name="autor", type="string", length=250, nullable=true)
     */
    private $autor;

    /**
     * @var string
     *
     * @ORM\Column(name="pdf", type="string", length=250, nullable=true)
     */
    private $pdf;

    /**
     * @var string
     *
     * @ORM\Column(name="imagen", type="string", length=250, nullable=true)
     */
    private $imagen;

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
     * @var \Actualidad\ComunBundle\Entity\AdminTipoNoticia
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\AdminTipoNoticia")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_noticia_id", referencedColumnName="id")
     * })
     */
    private $tipoNoticia;

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
     * Set titulo
     *
     * @param string $titulo
     *
     * @return AdminNoticia
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
     * Set resumen
     *
     * @param string $resumen
     *
     * @return AdminNoticia
     */
    public function setResumen($resumen)
    {
        $this->resumen = $resumen;
    
        return $this;
    }

    /**
     * Get resumen
     *
     * @return string
     */
    public function getResumen()
    {
        return $this->resumen;
    }

    /**
     * Set contenido
     *
     * @param string $contenido
     *
     * @return AdminNoticia
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
     * Set fechaPublicacion
     *
     * @param \DateTime $fechaPublicacion
     *
     * @return AdminNoticia
     */
    public function setFechaPublicacion($fechaPublicacion)
    {
        $this->fechaPublicacion = $fechaPublicacion;
    
        return $this;
    }

    /**
     * Get fechaPublicacion
     *
     * @return \DateTime
     */
    public function getFechaPublicacion()
    {
        return $this->fechaPublicacion;
    }

    /**
     * Set fechaVencimiento
     *
     * @param \DateTime $fechaVencimiento
     *
     * @return AdminNoticia
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
     * Set autor
     *
     * @param string $autor
     *
     * @return AdminNoticia
     */
    public function setAutor($autor)
    {
        $this->autor = $autor;
    
        return $this;
    }

    /**
     * Get autor
     *
     * @return string
     */
    public function getAutor()
    {
        return $this->autor;
    }

    /**
     * Set pdf
     *
     * @param string $pdf
     *
     * @return AdminNoticia
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
     * Set imagen
     *
     * @param string $imagen
     *
     * @return AdminNoticia
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
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     *
     * @return AdminNoticia
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
     * @return AdminNoticia
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
     * Set tipoNoticia
     *
     * @param \Actualidad\ComunBundle\Entity\AdminTipoNoticia $tipoNoticia
     *
     * @return AdminNoticia
     */
    public function setTipoNoticia(\Actualidad\ComunBundle\Entity\AdminTipoNoticia $tipoNoticia = null)
    {
        $this->tipoNoticia = $tipoNoticia;
    
        return $this;
    }

    /**
     * Get tipoNoticia
     *
     * @return \Actualidad\ComunBundle\Entity\AdminTipoNoticia
     */
    public function getTipoNoticia()
    {
        return $this->tipoNoticia;
    }

    /**
     * Set usuario
     *
     * @param \Actualidad\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return AdminNoticia
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
