<?php

namespace CoreBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ImportPost
{

    /**
     * @Assert\NotBlank()
     */
    protected $server;

    /**
     * @Assert\NotBlank()
     */
    protected $username;

        /**
     * @Assert\NotBlank()
     */
    protected $password;

    /**
     * @Assert\NotBlank()
     */
    protected $dbname;

    /**
     * @Assert\NotBlank()
     */
    protected $limit_start;

     /**
     * @Assert\NotBlank()
     */
    protected $limit_end;

    public function getServer()
    {
        return $this->server;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getDbname()
    {
        return $this->dbname;
    }

    public function getLimitStart()
    {
        return $this->limit_start;
    }

    public function getLimitEnd()
    {
        return $this->limit_end;
    }

    public function setServer($server)
    {
        $this->server = $server;

        return $this;
    }

    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function setDbname($dbname)
    {
        $this->dbname = $dbname;

        return $this;
    }

    public function setLimitStart($limit_start)
    {
        $this->limit_start = $limit_start;

        return $this;
    }

    public function setLimitEnd($limit_end)
    {
        $this->limit_end = $limit_end;

        return $this;
    }

}
