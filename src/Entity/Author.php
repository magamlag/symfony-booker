<?php

// src/Entity/Author.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\AuthorRepository;

/**
* @ORM\Entity(repositoryClass=AuthorRepository::class)
*/
class Author
{
//...

/**
* @ORM\Column(type="string", length=100)
*/
private $lastName;

/**
* @ORM\Column(type="string", length=100)
*/
private $firstName;

/**
* @ORM\Column(type="string", length=100, nullable=true)
*/
private $middleName;

// Getters и Setters...
}