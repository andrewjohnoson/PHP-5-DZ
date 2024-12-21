<?php

namespace App;

class Errors extends \Exception
{
    protected $errors = [];

    public function all()
    {
        return $this->errors;
    }

    public function add(\Exception $error)
    {
        $this->errors[] = $error;
    }

    public function empty() : bool
    {
        return empty($this->errors);
    }
}