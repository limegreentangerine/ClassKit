<?php

namespace ClassKit\Page;

/**
 * Class PageList.
 */
class PageList extends \Concrete\Core\Page\PageList
{
    /**
     * Filters the PageList by multiple topics.
     *
     * This method constructs a query that filters pages based on the given topics.
     * Each topic is represented as an associative array with a 'handle' and a 'topic'.
     * The filtering is done using the specified comparison operator ('AND' by default).
     *
     * @param array  $topics     An array of topics where each topic is an associative array
     *                           containing 'handle' and 'topic'. The 'topic' can be an instance
     *                           of \Concrete\Core\Tree\Node\Type\Topic, an integer, or a string.
     * @param string $comparison The logical comparison operator to use between topics ('AND' or 'OR').
     */
    public function filterByMultipleTopics(array $topics = [], string $comparison = 'AND')
    {
        if (is_array($topics) and count($topics) > 0) {
            $query = '';

            foreach ($topics as $key => $topicArray) {
                $handle = $topicArray['handle'];
                $topic = $topicArray['topic'];
                $index = ($key + 1);

                if ($topic instanceof \Concrete\Core\Tree\Node\Type\Topic) {
                    $query .= $handle . ' LIKE :treeNodeID' . $index . ' ' . $comparison . ' ';
                    $this->query->setParameter('treeNodeID' . $index, '%' . html_entity_decode($topic->getTreeNodeDisplayName()) . '%');
                } elseif (is_integer(intval($topic))) {
                    $query .= $handle . ' = :treeNodeID' . $index . ' ' . $comparison . ' ';
                    $this->query->setParameter('treeNodeID' . $index, intval($topic));
                } else {
                    $query .= $handle . ' = :treeNodeID' . $index . ' ' . $comparison . ' ';
                    $this->query->setParameter('treeNodeID' . $index, $topic);
                }
            }

            $query = substr($query, 0, -4); // Remove last " OR "

            $this->query->andWhere($query);
        }
    }

    /**
     * Returns the raw SQL query for this page list.
     *
     * @note This is for debugging purposes only. It will output the raw SQL query
     * to the screen.
     */
    public function getSql()
    {
        echo '<pre><code>';
        echo $this->query->getSql();
        echo '</code></pre>';
    }
}
