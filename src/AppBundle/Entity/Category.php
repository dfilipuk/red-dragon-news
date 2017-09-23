<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Category
 *
 * @ORM\Table(name="categories")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CategoryRepository")
 * @UniqueEntity(fields="name", message="Category with same name is already exists",
 *     groups={"editCategory", "newCategory", "newRootCategory"})
 */
class Category
{
    private const MAX_NESTING_LEVEL = 2;

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
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     * @Assert\NotBlank(groups={"editCategory", "newCategory", "newRootCategory"})
     * @Assert\Length(max=255, maxMessage="Name too long", groups={"editCategory", "newCategory", "newRootCategory"})
     */
    private $name;

    /**
     * @ORM\Column(name="level", type="integer")
     */
    private $level;

    /**
     * @OneToMany(targetEntity="Category", mappedBy="parent")
     */
    private $children;

    /**
     * Many Categories have One Category.
     * @ManyToOne(targetEntity="Category", inversedBy="children")
     * @JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

    /**
     * @OneToMany(targetEntity="Article", mappedBy="category")
     */
    private $articles;

    private $isRootCategory;

    /**
     * @Assert\NotBlank(groups={"newCategory"}, message="Parent category name should not be blank")
     * @Assert\Blank(groups={"newRootCategory"}, message="Parent category name should be blank")
     */
    private $parentName;

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Category
     */
    public function setName(string $name): Category
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName():? string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * @param mixed $articles
     */
    public function setArticles($articles)
    {
        $this->articles = $articles;
    }

    /**
     * @return mixed
     */
    public function getParent():? Category
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent(Category $parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param mixed $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * @return mixed
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param mixed $level
     */
    public function setLevel(int $level)
    {
        $this->level = $level;
    }

    /**
     * @return mixed
     */
    public function getIsRootCategory(): bool
    {
        return $this->isRootCategory;
    }

    /**
     * @return mixed
     */
    public function getParentName():? string
    {
        return $this->parentName;
    }

    /**
     * @param mixed $isRootCategory
     */
    public function setIsRootCategory(bool $isRootCategory)
    {
        $this->isRootCategory = $isRootCategory;
    }

    /**
     * @param mixed $parentName
     */
    public function setParentName(string $parentName)
    {
        $this->parentName = $parentName;
    }

    public function __construct() {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->articles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->isRootCategory = false;
    }

    public function isLeafOfTree(): bool
    {
        return $this->level === self::MAX_NESTING_LEVEL;
    }
}

