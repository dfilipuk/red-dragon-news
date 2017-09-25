<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Article
 *
 * @ORM\Table(name="articles")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ArticleRepository")
 * @UniqueEntity(fields="title", message="Article with same title is already exists")
 */
class Article
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
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text")
     */
    private $text;

    /**
     * @var string
     *
     * @ORM\Column(name="picture", type="string", length=255, nullable=true)
     * @Assert\Image()
     */
    private $picture;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var \int
     *
     * @ORM\Column(name="views_count", type="integer")
     */
    private $viewsCount;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="author_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $author;

    /**
     * @ManyToOne(targetEntity="Category", inversedBy="articles")
     * @JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

    /**
     * @ManyToMany(targetEntity="Article", mappedBy="similarArticles")
     */
    private $articlesWithThis;

    /**
     * @ManyToMany(targetEntity="Article", inversedBy="articlesWithThis")
     * @JoinTable(name="similar_articles",
     *      joinColumns={@JoinColumn(name="base_article_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="similar_article_id", referencedColumnName="id")}
     *      )
     */
    private $similarArticles;


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
     * Set title
     *
     * @param string $title
     *
     * @return Article
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return Article
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set picture
     *
     * @param string $picture
     *
     * @return Article
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture
     *
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Article
     */
    public function setDate($date)
    {
        $this->date = $date;

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
     * @return int
     */
    public function getViewsCount()
    {
        return $this->viewsCount;
    }

    /**
     * @param int $viewsCount
     */
    public function setViewsCount($viewsCount)
    {
        $this->viewsCount = $viewsCount;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $authorID
     */
    public function setAuthor($authorID)
    {
        $this->author = $authorID;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $categoryID
     */
    public function setCategory($categoryID)
    {
        $this->category = $categoryID;
    }

    /**
     * @return mixed
     */
    public function getSimilarArticles()
    {
        return $this->similarArticles;
    }

    /**
     * @param mixed $similarArticles
     */
    public function setSimilarArticles(array $similarArticles)
    {
        $this->similarArticles = new \Doctrine\Common\Collections\ArrayCollection($similarArticles);
    }

    /**
     * @return mixed
     */
    public function getArticlesWithThis()
    {
        return $this->articlesWithThis;
    }

    /**
     * @param mixed $articlesWithThis
     */
    public function setArticlesWithThis($articlesWithThis)
    {
        $this->articlesWithThis = $articlesWithThis;
    }

    public function __construct() {
        $this->articlesWithThis = new \Doctrine\Common\Collections\ArrayCollection();
        $this->similarArticles = new \Doctrine\Common\Collections\ArrayCollection();
    }
}

