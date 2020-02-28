<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminUsuarioSeccion
 *
 * @ORM\Table(name="admin_usuario_seccion", indexes={@ORM\Index(name="IDX_A54B5369DB38439E", columns={"usuario_id"}), @ORM\Index(name="IDX_A54B53697A5A413A", columns={"seccion_id"})})
 * @ORM\Entity
 */
class AdminUsuarioSeccion
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_usuario_seccion_id_seq", allocationSize=1, initialValue=1)
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
     * @var \Actualidad\ComunBundle\Entity\AdminSeccion
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\AdminSeccion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="seccion_id", referencedColumnName="id")
     * })
     */
    private $seccion;



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
     * @return AdminUsuarioSeccion
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
     * Set seccion
     *
     * @param \Actualidad\ComunBundle\Entity\AdminSeccion $seccion
     *
     * @return AdminUsuarioSeccion
     */
    public function setSeccion(\Actualidad\ComunBundle\Entity\AdminSeccion $seccion = null)
    {
        $this->seccion = $seccion;
    
        return $this;
    }

    /**
     * Get seccion
     *
     * @return \Actualidad\ComunBundle\Entity\AdminSeccion
     */
    public function getSeccion()
    {
        return $this->seccion;
    }
}
