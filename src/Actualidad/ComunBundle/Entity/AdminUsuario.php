<?php

namespace Actualidad\ComunBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminUsuario
 *
 * @ORM\Table(name="admin_usuario", indexes={@ORM\Index(name="IDX_E65932D4E8608214", columns={"ciudad_id"}), @ORM\Index(name="IDX_E65932D44E7121AF", columns={"provincia_id"}), @ORM\Index(name="IDX_E65932D491A441CC", columns={"grado_id"}), @ORM\Index(name="IDX_E65932D44BAB96C", columns={"rol_id"})})
 * @ORM\Entity
 */
class AdminUsuario
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_usuario_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="login", type="string", length=50, nullable=true)
     */
    private $login;

    /**
     * @var string
     *
     * @ORM\Column(name="clave", type="string", length=50, nullable=true)
     */
    private $clave;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=50, nullable=true)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="apellido", type="string", length=50, nullable=true)
     */
    private $apellido;

    /**
     * @var string
     *
     * @ORM\Column(name="correo", type="string", length=100, nullable=true)
     */
    private $correo;

    /**
     * @var boolean
     *
     * @ORM\Column(name="activo", type="boolean", nullable=true)
     */
    private $activo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_nacimiento", type="date", nullable=true)
     */
    private $fechaNacimiento;

    /**
     * @var string
     *
     * @ORM\Column(name="sector", type="string", length=100, nullable=true)
     */
    private $sector;

    /**
     * @var string
     *
     * @ORM\Column(name="zona", type="string", length=100, nullable=true)
     */
    private $zona;

    /**
     * @var string
     *
     * @ORM\Column(name="foto", type="string", length=500, nullable=true)
     */
    private $foto;

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
     * @ORM\Column(name="cookies", type="string", length=100, nullable=true)
     */
    private $cookies;

    /**
     * @var \Actualidad\ComunBundle\Entity\AdminCiudad
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\AdminCiudad")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ciudad_id", referencedColumnName="id")
     * })
     */
    private $ciudad;

    /**
     * @var \Actualidad\ComunBundle\Entity\AdminProvincia
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\AdminProvincia")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="provincia_id", referencedColumnName="id")
     * })
     */
    private $provincia;

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
     * @var \Actualidad\ComunBundle\Entity\AdminRol
     *
     * @ORM\ManyToOne(targetEntity="Actualidad\ComunBundle\Entity\AdminRol")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="rol_id", referencedColumnName="id")
     * })
     */
    private $rol;



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
     * Set login
     *
     * @param string $login
     *
     * @return AdminUsuario
     */
    public function setLogin($login)
    {
        $this->login = $login;
    
        return $this;
    }

    /**
     * Get login
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set clave
     *
     * @param string $clave
     *
     * @return AdminUsuario
     */
    public function setClave($clave)
    {
        $this->clave = $clave;
    
        return $this;
    }

    /**
     * Get clave
     *
     * @return string
     */
    public function getClave()
    {
        return $this->clave;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     *
     * @return AdminUsuario
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
     * Set apellido
     *
     * @param string $apellido
     *
     * @return AdminUsuario
     */
    public function setApellido($apellido)
    {
        $this->apellido = $apellido;
    
        return $this;
    }

    /**
     * Get apellido
     *
     * @return string
     */
    public function getApellido()
    {
        return $this->apellido;
    }

    /**
     * Set correo
     *
     * @param string $correo
     *
     * @return AdminUsuario
     */
    public function setCorreo($correo)
    {
        $this->correo = $correo;
    
        return $this;
    }

    /**
     * Get correo
     *
     * @return string
     */
    public function getCorreo()
    {
        return $this->correo;
    }

    /**
     * Set activo
     *
     * @param boolean $activo
     *
     * @return AdminUsuario
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;
    
        return $this;
    }

    /**
     * Get activo
     *
     * @return boolean
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * Set fechaNacimiento
     *
     * @param \DateTime $fechaNacimiento
     *
     * @return AdminUsuario
     */
    public function setFechaNacimiento($fechaNacimiento)
    {
        $this->fechaNacimiento = $fechaNacimiento;
    
        return $this;
    }

    /**
     * Get fechaNacimiento
     *
     * @return \DateTime
     */
    public function getFechaNacimiento()
    {
        return $this->fechaNacimiento;
    }

    /**
     * Set sector
     *
     * @param string $sector
     *
     * @return AdminUsuario
     */
    public function setSector($sector)
    {
        $this->sector = $sector;
    
        return $this;
    }

    /**
     * Get sector
     *
     * @return string
     */
    public function getSector()
    {
        return $this->sector;
    }

    /**
     * Set zona
     *
     * @param string $zona
     *
     * @return AdminUsuario
     */
    public function setZona($zona)
    {
        $this->zona = $zona;
    
        return $this;
    }

    /**
     * Get zona
     *
     * @return string
     */
    public function getZona()
    {
        return $this->zona;
    }

    /**
     * Set foto
     *
     * @param string $foto
     *
     * @return AdminUsuario
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
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     *
     * @return AdminUsuario
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
     * @return AdminUsuario
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
     * Set cookies
     *
     * @param string $cookies
     *
     * @return AdminUsuario
     */
    public function setCookies($cookies)
    {
        $this->cookies = $cookies;
    
        return $this;
    }

    /**
     * Get cookies
     *
     * @return string
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * Set ciudad
     *
     * @param \Actualidad\ComunBundle\Entity\AdminCiudad $ciudad
     *
     * @return AdminUsuario
     */
    public function setCiudad(\Actualidad\ComunBundle\Entity\AdminCiudad $ciudad = null)
    {
        $this->ciudad = $ciudad;
    
        return $this;
    }

    /**
     * Get ciudad
     *
     * @return \Actualidad\ComunBundle\Entity\AdminCiudad
     */
    public function getCiudad()
    {
        return $this->ciudad;
    }

    /**
     * Set provincia
     *
     * @param \Actualidad\ComunBundle\Entity\AdminProvincia $provincia
     *
     * @return AdminUsuario
     */
    public function setProvincia(\Actualidad\ComunBundle\Entity\AdminProvincia $provincia = null)
    {
        $this->provincia = $provincia;
    
        return $this;
    }

    /**
     * Get provincia
     *
     * @return \Actualidad\ComunBundle\Entity\AdminProvincia
     */
    public function getProvincia()
    {
        return $this->provincia;
    }

    /**
     * Set grado
     *
     * @param \Actualidad\ComunBundle\Entity\AdminGrado $grado
     *
     * @return AdminUsuario
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
     * Set rol
     *
     * @param \Actualidad\ComunBundle\Entity\AdminRol $rol
     *
     * @return AdminUsuario
     */
    public function setRol(\Actualidad\ComunBundle\Entity\AdminRol $rol = null)
    {
        $this->rol = $rol;
    
        return $this;
    }

    /**
     * Get rol
     *
     * @return \Actualidad\ComunBundle\Entity\AdminRol
     */
    public function getRol()
    {
        return $this->rol;
    }
}
