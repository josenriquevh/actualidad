<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EaCertificado
 *
 * @ORM\Table(name="ea_certificado", indexes={@ORM\Index(name="IDX_A443CECC521E1991", columns={"empresa_id"}), @ORM\Index(name="IDX_A443CECC1AAC87BB", columns={"tipo_certificado_id"}), @ORM\Index(name="IDX_A443CECC91A441CC", columns={"grado_id"})})
 * @ORM\Entity
 */
class EaCertificado
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ea_certificado_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="entidad_id", type="integer", nullable=true)
     */
    private $entidadId;

    /**
     * @var string
     *
     * @ORM\Column(name="imagen", type="string", length=250, nullable=true)
     */
    private $imagen;

    /**
     * @var string
     *
     * @ORM\Column(name="encabezado", type="text", nullable=true)
     */
    private $encabezado;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="text", nullable=true)
     */
    private $descripcion;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_creacion", type="date", nullable=true)
     */
    private $fechaCreacion;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_modificacion", type="date", nullable=true)
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
     * @var \Actualidad\ComunBundle\Entity\EaTipoCertificado
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\EaTipoCertificado")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_certificado_id", referencedColumnName="id")
     * })
     */
    private $tipoCertificado;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set entidadId
     *
     * @param integer $entidadId
     *
     * @return EaCertificado
     */
    public function setEntidadId($entidadId)
    {
        $this->entidadId = $entidadId;
    
        return $this;
    }

    /**
     * Get entidadId
     *
     * @return integer
     */
    public function getEntidadId()
    {
        return $this->entidadId;
    }

    /**
     * Set imagen
     *
     * @param string $imagen
     *
     * @return EaCertificado
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
     * Set encabezado
     *
     * @param string $encabezado
     *
     * @return EaCertificado
     */
    public function setEncabezado($encabezado)
    {
        $this->encabezado = $encabezado;
    
        return $this;
    }

    /**
     * Get encabezado
     *
     * @return string
     */
    public function getEncabezado()
    {
        return $this->encabezado;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return EaCertificado
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
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     *
     * @return EaCertificado
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
     * @return EaCertificado
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
     * @return EaCertificado
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
     * Set tipoCertificado
     *
     * @param \Actualidad\ComunBundle\Entity\EaTipoCertificado $tipoCertificado
     *
     * @return EaCertificado
     */
    public function setTipoCertificado(\Actualidad\ComunBundle\Entity\EaTipoCertificado $tipoCertificado = null)
    {
        $this->tipoCertificado = $tipoCertificado;
    
        return $this;
    }

    /**
     * Get tipoCertificado
     *
     * @return \Actualidad\ComunBundle\Entity\EaTipoCertificado
     */
    public function getTipoCertificado()
    {
        return $this->tipoCertificado;
    }

    /**
     * Set grado
     *
     * @param \Actualidad\ComunBundle\Entity\AdminGrado $grado
     *
     * @return EaCertificado
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
}
