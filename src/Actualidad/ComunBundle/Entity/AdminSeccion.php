<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminSeccion
 *
 * @ORM\Table(name="admin_seccion", indexes={@ORM\Index(name="IDX_248197407FDC9E6F", columns={"colegio_id"}), @ORM\Index(name="IDX_2481974091A441CC", columns={"grado_id"})})
 * @ORM\Entity
 */
class AdminSeccion
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_seccion_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=10, nullable=true)
     */
    private $nombre;

    /**
     * @var \Actualidad\ComunBundle\Entity\AdminColegio
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\AdminColegio")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="colegio_id", referencedColumnName="id")
     * })
     */
    private $colegio;

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
     * Set nombre
     *
     * @param string $nombre
     *
     * @return AdminSeccion
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    
        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set colegio
     *
     * @param \Actualidad\ComunBundle\Entity\AdminColegio $colegio
     *
     * @return AdminSeccion
     */
    public function setColegio(\Actualidad\ComunBundle\Entity\AdminColegio $colegio = null)
    {
        $this->colegio = $colegio;
    
        return $this;
    }

    /**
     * Get colegio
     *
     * @return \Actualidad\ComunBundle\Entity\AdminColegio
     */
    public function getColegio()
    {
        return $this->colegio;
    }

    /**
     * Set grado
     *
     * @param \Actualidad\ComunBundle\Entity\AdminGrado $grado
     *
     * @return AdminSeccion
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
