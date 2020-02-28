<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EaProfesorAlumno
 *
 * @ORM\Table(name="ea_profesor_alumno", indexes={@ORM\Index(name="IDX_14530F87E52BD977", columns={"profesor_id"}), @ORM\Index(name="IDX_14530F87FC28E5EE", columns={"alumno_id"}), @ORM\Index(name="IDX_14530F877A5A413A", columns={"seccion_id"})})
 * @ORM\Entity
 */
class EaProfesorAlumno
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ea_profesor_alumno_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_seguimiento", type="date", nullable=true)
     */
    private $fechaSeguimiento;

    /**
     * @var \Actualidad\ComunBundle\Entity\AdminUsuario
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\AdminUsuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="profesor_id", referencedColumnName="id")
     * })
     */
    private $profesor;

    /**
     * @var \Actualidad\ComunBundle\Entity\AdminUsuario
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\AdminUsuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="alumno_id", referencedColumnName="id")
     * })
     */
    private $alumno;

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
     * Set fechaSeguimiento
     *
     * @param \DateTime $fechaSeguimiento
     *
     * @return EaProfesorAlumno
     */
    public function setFechaSeguimiento($fechaSeguimiento)
    {
        $this->fechaSeguimiento = $fechaSeguimiento;
    
        return $this;
    }

    /**
     * Get fechaSeguimiento
     *
     * @return \DateTime
     */
    public function getFechaSeguimiento()
    {
        return $this->fechaSeguimiento;
    }

    /**
     * Set profesor
     *
     * @param \Actualidad\ComunBundle\Entity\AdminUsuario $profesor
     *
     * @return EaProfesorAlumno
     */
    public function setProfesor(\Actualidad\ComunBundle\Entity\AdminUsuario $profesor = null)
    {
        $this->profesor = $profesor;
    
        return $this;
    }

    /**
     * Get profesor
     *
     * @return \Actualidad\ComunBundle\Entity\AdminUsuario
     */
    public function getProfesor()
    {
        return $this->profesor;
    }

    /**
     * Set alumno
     *
     * @param \Actualidad\ComunBundle\Entity\AdminUsuario $alumno
     *
     * @return EaProfesorAlumno
     */
    public function setAlumno(\Actualidad\ComunBundle\Entity\AdminUsuario $alumno = null)
    {
        $this->alumno = $alumno;
    
        return $this;
    }

    /**
     * Get alumno
     *
     * @return \Actualidad\ComunBundle\Entity\AdminUsuario
     */
    public function getAlumno()
    {
        return $this->alumno;
    }

    /**
     * Set seccion
     *
     * @param \Actualidad\ComunBundle\Entity\AdminSeccion $seccion
     *
     * @return EaProfesorAlumno
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
