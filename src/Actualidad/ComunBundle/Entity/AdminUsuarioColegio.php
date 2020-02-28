<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminUsuarioColegio
 *
 * @ORM\Table(name="admin_usuario_colegio", indexes={@ORM\Index(name="IDX_EFF69ED6DB38439E", columns={"usuario_id"}), @ORM\Index(name="IDX_EFF69ED67FDC9E6F", columns={"colegio_id"})})
 * @ORM\Entity
 */
class AdminUsuarioColegio
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_usuario_colegio_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

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
     * @var \Actualidad\ComunBundle\Entity\AdminColegio
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\AdminColegio")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="colegio_id", referencedColumnName="id")
     * })
     */
    private $colegio;



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
     * Set usuario
     *
     * @param \Actualidad\ComunBundle\Entity\AdminUsuario $usuario
     *
     * @return AdminUsuarioColegio
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
     * Set colegio
     *
     * @param \Actualidad\ComunBundle\Entity\AdminColegio $colegio
     *
     * @return AdminUsuarioColegio
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
}
