<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EaForo
 *
 * @ORM\Table(name="ea_foro", indexes={@ORM\Index(name="IDX_A0CB7BA657991ECF", columns={"pagina_id"}), @ORM\Index(name="IDX_A0CB7BA6DB38439E", columns={"usuario_id"}), @ORM\Index(name="IDX_A0CB7BA6F5FF53F6", columns={"foro_id"}), @ORM\Index(name="IDX_A0CB7BA6E6067256", columns={"tipo_foro_id"})})
 * @ORM\Entity
 */
class EaForo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ea_foro_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="tema", type="string", length=350, nullable=true)
     */
    private $tema;

    /**
     * @var string
     *
     * @ORM\Column(name="mensaje", type="text", nullable=true)
     */
    private $mensaje;

    /**
     * @var string
     *
     * @ORM\Column(name="pdf", type="string", length=250, nullable=true)
     */
    private $pdf;

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
     * @var \Actualidad\ComunBundle\Entity\EaForo
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\EaForo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="foro_id", referencedColumnName="id")
     * })
     */
    private $foro;

    /**
     * @var \Actualidad\ComunBundle\Entity\EaTipoForo
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\EaTipoForo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_foro_id", referencedColumnName="id")
     * })
     */
    private $tipoForo;



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
     * Set tema
     *
     * @param string $tema
     *
     * @return EaForo
     */
    public function setTema($tema)
    {
        $this->tema = $tema;
    
        return $this;
    }

    /**
     * Get tema
     *
     * @return string
     */
    public function getTema()
    {
        return $this->tema;
    }

    /**
     * Set mensaje
     *
     * @param string $mensaje
     *
     * @return EaForo
     */
    public function setMensaje($mensaje)
    {
        $this->mensaje = $mensaje;
    
        return $this;
    }

    /**
     * Get mensaje
     *
     * @return string
     */
    public function getMensaje()
    {
        return $this->mensaje;
    }

    /**
     * Set pdf
     *
     * @param string $pdf
     *
     * @return EaForo
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
     * Set fechaPublicacion
     *
     * @param \DateTime $fechaPublicacion
     *
     * @return EaForo
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
     * @return EaForo
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
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     *
     * @return EaForo
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
     * @return EaForo
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
     * Set pagina
     *
     * @param \Actualidad\ComunBundle\Entity\EaPagina $pagina
     *
     * @return EaForo
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
     * @return EaForo
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
     * Set foro
     *
     * @param \Actualidad\ComunBundle\Entity\EaForo $foro
     *
     * @return EaForo
     */
    public function setForo(\Actualidad\ComunBundle\Entity\EaForo $foro = null)
    {
        $this->foro = $foro;
    
        return $this;
    }

    /**
     * Get foro
     *
     * @return \Actualidad\ComunBundle\Entity\EaForo
     */
    public function getForo()
    {
        return $this->foro;
    }

    /**
     * Set tipoForo
     *
     * @param \Actualidad\ComunBundle\Entity\EaTipoForo $tipoForo
     *
     * @return EaForo
     */
    public function setTipoForo(\Actualidad\ComunBundle\Entity\EaTipoForo $tipoForo = null)
    {
        $this->tipoForo = $tipoForo;
    
        return $this;
    }

    /**
     * Get tipoForo
     *
     * @return \Actualidad\ComunBundle\Entity\EaTipoForo
     */
    public function getTipoForo()
    {
        return $this->tipoForo;
    }
}
