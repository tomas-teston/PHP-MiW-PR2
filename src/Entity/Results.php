<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Results
 *
 * @ORM\Table(name="results", indexes={@ORM\Index(name="FK_USER_ID_idx", columns={"user_id"})})
 * @ORM\Entity
 */
class Results implements \JsonSerializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="result", type="integer", nullable=false)
     */
    private $result;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time", type="datetime", nullable=false)
     */
    private $time;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * Results constructor.
     * @param int $id
     * @param int $result
     * @param DateTime $time
     * @param Users $Users
     * @throws \Exception
     */
    public function __construct(int $id = 0, int $result = 0, DateTime $time = null, $Users = null)
    {
        $this->id = $id;
        $this->result = $result;
        $this->time = $time ?? new DateTime("now");
        $this->user = $Users ?? new Users();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Results
     */
    public function setId(int $id): Results
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getResult(): int
    {
        return $this->result;
    }

    /**
     * @param int $result
     * @return Results
     */
    public function setResult(int $result): Results
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getTime(): DateTime
    {
        return $this->time;
    }

    /**
     * @param DateTime $time
     * @return Results
     */
    public function setTime(DateTime $time): Results
    {
        $this->time = $time;
        return $this;
    }

    /**
     * @return Users
     */
    public function getUser(): Users
    {
        return $this->user;
    }

    /**
     * @param Users $user
     * @return Results
     */
    public function setUser(Users $user): Results
    {
        $this->user = $user;
        return $this;
    }


    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'result' => [
                'id' => $this->getId(),
                'result' => $this->getResult(),
                'time' => $this->getTime(),
                'user' => $this->getUser()
            ]
        ];
    }
}
