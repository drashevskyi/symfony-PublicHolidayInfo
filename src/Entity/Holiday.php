<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Holiday
 *
 * @ORM\Entity
 * @ORM\Table(name="holidays")
 */
class Holiday
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     * 
     * @ORM\Column(name="year", type="integer")
     */
    private $year;

    /**
     * @var string
     * 
     * @ORM\Column(name="country", type="string", length=128)
     */
    private $country;
    
    /**
     * @var string
     * 
     * @ORM\Column(name="name", type="string", length=128)
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

     /**
     * Get year
     *
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set year
     *
     * @param int $year
     *
     * @return Holiday
     */
    public function setYear(int $year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }


    /**
     * Set country
     *
     * @param string $country
     *
     * @return Holiday
     */
    public function setCountry(string $country)
    {
        $this->country = $country;

        return $this;
    }
    
    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * Set name
     *
     * @param string $name
     *
     * @return Holiday
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Holiday
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }
}