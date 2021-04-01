<?php

namespace AppBundle\Repository;

use Doctrine\ORM\Query;

/**
 * ArticleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ArticleRepository extends \Doctrine\ORM\ EntityRepository
{
    /**
     * @param array $categoriesID
     * @param bool $isOrderByDate
     * @param bool $isAscending
     * @return array|null
     */
    public function findNewsByCategoryWithSort(array $categoriesID, bool $isOrderByDate, bool $isAscending): ?array
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT a FROM AppBundle:Article a WHERE a.category IN(:categoriesID)' .
                ' ORDER BY a.' . ($isOrderByDate ? 'date' : 'viewsCount') . ' ' . ($isAscending ? 'ASC' : 'DESC')
            )
            ->setParameter('categoriesID', $categoriesID)
            ->getResult();
    }

    /**
     * @param bool $isOrderByDate
     * @param bool $isAscending
     * @return array|null
     */
    public function findAllNewsWithSorting(bool $isOrderByDate, bool $isAscending): ?array
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT a FROM AppBundle:Article a' .
                ' ORDER BY a.' . ($isOrderByDate ? 'date' : 'viewsCount') . ' ' . ($isAscending ? 'ASC' : 'DESC')
            )
            ->getResult();
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function findNewsById(int $id): ?array
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT article, similar 
                      FROM 
                        AppBundle:Article article 
                      LEFT JOIN 
                        article.similarArticles similar
                      WHERE 
                        article.id = :id'
            )
            ->setParameter('id', $id)
            ->getResult();
    }

    /**
     * @param string $sortField
     * @param bool $isAscending
     * @param array $filters
     * @param int $offset
     * @param int $itemsPerPage
     * @return array|null
     */
    public function getArticlesList(string $sortField, bool $isAscending, array $filters, int $offset, int $itemsPerPage): ?array
    {
        $query = 'SELECT a, author, category FROM AppBundle:Article a
                    LEFT JOIN 
                        a.author author
                    LEFT JOIN 
                        a.category category';
        return $this->getSortedAndFilteredArticles($query, $sortField, $isAscending, $filters)
            ->setFirstResult($offset)
            ->setMaxResults($itemsPerPage)
            ->getResult();
    }

    /**
     * @param string $sortField
     * @param bool $isAscending
     * @param array $filters
     * @return int
     */
    public function getArticlesCount(string $sortField, bool $isAscending, array $filters): int
    {
        $query = 'SELECT COUNT(a) FROM AppBundle:Article a
                    LEFT JOIN 
                        a.author author
                    LEFT JOIN 
                        a.category category';
        return $this->getSortedAndFilteredArticles($query, $sortField, $isAscending, $filters)
            ->getSingleScalarResult();
    }

    /**
     * @param string $query
     * @param string $sortField
     * @param bool $isAscending
     * @param array $filters
     * @return Query
     */
    private function getSortedAndFilteredArticles(string $query, string $sortField, bool $isAscending, array $filters): Query
    {
        $query .= $this->getDQLWithFilters($filters);
        $query .= ' ORDER BY ' . $sortField . ' ' . ($isAscending ? 'ASC' : 'DESC');

        $temp = [];
        for ($i = 0; $i < count($filters); $i++) {
            $temp[$i] = $filters[$i][1];
        }
        return $this->getEntityManager()
            ->createQuery($query)
            ->setParameters($temp);
    }

    /**
     * @param array $filters
     * @return string
     */
    private function getDQLWithFilters(array $filters): string
    {
        $result = '';
        if (key_exists(0, $filters)) {
            $result = ' WHERE ' . $filters[0][0] . ' LIKE ?0';
            for ($i = 1; $i < count($filters); $i++) {
                $result .= ' AND ' . $filters[$i][0] . ' LIKE ' . '?' . $i;
            }
        }

        return $result;
    }

    /**
     * @param \DateTime $dateFrom
     * @return array|null
     */
    public function getArticlesAfterTime(\DateTime $dateFrom): ?array
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT a FROM AppBundle:Article a 
                                WHERE a.date >= :dateFrom'
            )
            ->setParameter('dateFrom', $dateFrom)
            ->getResult();
    }

    /**
     * @param array $similars
     * @return array|null
     */
    public function getSimilarArticles(array $similars): ?array
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT a FROM AppBundle:Article a
                                WHERE a.id IN(:similars)'
            )
            ->setParameter('similars', $similars)
            ->getResult();
    }
}