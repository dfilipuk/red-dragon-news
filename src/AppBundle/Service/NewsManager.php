<?php
/**
 * Created by PhpStorm.
 * User: Алексей
 * Date: 07.09.2017
 * Time: 14:35
 */

namespace AppBundle\Service;

use AppBundle\Entity\Article;
use AppBundle\Entity\Category;
use AppBundle\Entity\User;
use \Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Filesystem\Filesystem;

class NewsManager
{
    private const TREE_ROOT = 0;
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    private function categoriesToArray(array $categories): array
    {
        $categoryArray = [];
        foreach ($categories as $category){
            $temp = [];

            $temp['id'] = $category->getId();
            $temp['name'] = $category->getName();
            if ($category->getParent() === null){
                $temp['parent_id'] = self::TREE_ROOT;
            } else {
                $temp['parent_id'] = $category->getParent()->getId();
            }
            $categoryArray[$temp['id']] = $temp;
        }
        return $categoryArray;
    }

    private function makeCategoriesTree(array $items, int $root = self::TREE_ROOT): array
    {
        $tree = [];
        foreach($items as $item) {
            if($item['parent_id'] == $root && $item['id'] != $item['parent_id']) {
                unset($items[$item['id']]);
                $tree[$item['id']] = array(
                    $item['id'] => $item,
                    'children' => $this->makeCategoriesTree($items, $item['id'] )
                );
            }
        }
        return $tree;
    }

    private function getCategoriesAndChildrensID(array $categoryAndChildrensTree, array &$categoriesID): void
    {
        if(!is_null($categoryAndChildrensTree) && count($categoryAndChildrensTree) > 0) {
            foreach($categoryAndChildrensTree as $key=>$node) {
                array_push($categoriesID,$node[$key]['id']);
                $this->getCategoriesAndChildrensID($node['children'], $categoriesID);
            }
        }
    }

    private function transformToTree(array $categories): array
    {
        $transformedArray = $this->categoriesToArray($categories);
        return $this->makeCategoriesTree($transformedArray);

    }

    public function getSortedCategories(): array
    {
        $categories = $this->findAllCategories();
        $categories = $this->transformToTree($categories);


        return $categories;
    }

    public function findAllNews(bool $isOrderByDate, bool $isAscending): array
    {
        return $this->doctrine->getManager()
            ->getRepository(Article::class)
            ->findAllNewsWithSorting($isOrderByDate, $isAscending);
    }

    public function findAllCategories(): array
    {
        return $this->doctrine->getManager()->getRepository(Category::class)->findAll();
    }

    public function findGeneralCategories(): array
    {
        return $this->doctrine->getManager()->getRepository(Category::class)->findGeneralCategories();
    }

    public function getCategoryAndChildrenID(string $category): ?array
    {
        $categories = $this->findAllCategories();
        $transformedArray = array_values($this->categoriesToArray($categories));
        $category = array_search($category, array_column($transformedArray, 'name'));
        $categoryWithChildrensID = [];
        if ($category === false){
            return $categoryWithChildrensID;
        }
        $categoryID = $transformedArray[$category]['id'];
        $categoryAndChildrensTree = $this->makeCategoriesTree($transformedArray, $categoryID);
        array_push($categoryWithChildrensID, $categoryID);
        $this->getCategoriesAndChildrensID($categoryAndChildrensTree, $categoryWithChildrensID);
        return $categoryWithChildrensID;
    }

    public function findNewsByCategory(string $category, bool $isOrderByDate, bool $isAscending): ?array
    {
        $categoriesID = $this->getCategoryAndChildrenID($category);
        $result = [];
        if (array_key_exists(0, $categoriesID)){
            $result = $this->doctrine->getManager()
                ->getRepository(Article::class)
                ->findNewsByCategoryWithSort($categoriesID, $isOrderByDate, $isAscending);
        }
        return $result;
    }

    public function findNewsById(int $id): ?Article
    {
        $result = $this->doctrine->getManager()->getRepository(Article::class)->findNewsById($id);
        return array_key_exists(0, $result) ? $result[0] : null;
    }

    public function updateWatchCount(int $id): void
    {
        $article = $this->findNewsById($id);
        $article->setViewsCount($article->getViewsCount() + 1);
        $manager = $this->doctrine->getManager();
        $manager->persist($article);
        $manager->flush();
    }

    private function getSimilarNewsById(array $similars): array
    {
        $similars = array_unique($similars);
        $manager = $this->doctrine->getManager();
        $repository = $manager->getRepository(Article::class);
        return $repository->getSimilarArticles($similars);

    }

    public function createArticle(Article $article,  \Symfony\Component\Form\Form $form,User $user, Category $category, string $savePath, ?array $similars)
    {
        $manager = $this->doctrine->getManager();
        $file = $article->getPicture();

        if ($article->getText() === null) {
            $article->setText('');
        }

        if ($file !== null){
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $savePath,
                $fileName
            );
            $article->setPicture($fileName);
        }

        if($similars !== null){
            $article->setSimilarArticles($this->getSimilarNewsById($similars));
        } else{
            $article->setSimilarArticles([]);
        }

        $time = new \DateTime();
        $article->setDate($time);
        $article->setAuthor($user);
        $article->setCategory($category);
        $article->setViewsCount(0);
        $manager->persist($article);
        $manager->flush();
    }


    public function editArticle(Article $article,  \Symfony\Component\Form\Form $form, Category $category, string $savePath, ?string $oldPicture, ?array $similars)
    {
        $manager = $this->doctrine->getManager();
        $file = $article->getPicture();

        if ($article->getText() === null) {
            $article->setText('');
        }

        if ($file !== null){
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $savePath,
                $fileName
            );
            $article->setPicture($fileName);
        } else{
            $article->setPicture($oldPicture);
        }

        if($similars !== null){
            $article->setSimilarArticles($this->getSimilarNewsById($similars));
        } else{
            $article->setSimilarArticles([]);
        }


        $article->setCategory($category);
        $manager->persist($article);
        $manager->flush();
    }

    public function deleteArticleById(int $id, string $savePath)
    {
        $manager = $this->doctrine->getManager();
        $article = $this->findNewsById($id);
        if ($article !== null) {
            $picturePath = $article->getPicture();
            $fs = new Filesystem();
            if ($picturePath !== null){
                $path = $savePath.'/'.$picturePath;
                if($fs->exists($path)){
                    $fs->remove($path);
                }
            }
            $manager->remove($article);
            $manager->flush();
        }
    }
}