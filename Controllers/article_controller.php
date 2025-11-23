<?php
/**
 * Article Controller
 * Creates an instance of the article class and runs the methods
 */

require_once __DIR__ . '/../Classes/article_class.php';

class article_controller
{
    private $article;

    public function __construct()
    {
        $this->article = new article_class();
    }

    /**
     * Add a new article
     * @param array $kwargs Article data
     * @return array Result with status and message
     */
    public function add_article_ctr($kwargs)
    {
        return $this->article->add($kwargs);
    }

    /**
     * Get all articles
     * @return array All articles
     */
    public function get_all_articles_ctr()
    {
        return $this->article->get_all();
    }

    /**
     * Get a single article by ID
     * @param int $article_id Article ID
     * @return array|false Article data or false if not found
     */
    public function get_article_ctr($article_id)
    {
        return $this->article->get_one($article_id);
    }

    /**
     * Update an article
     * @param array $kwargs Article data
     * @return array Result with status and message
     */
    public function update_article_ctr($kwargs)
    {
        return $this->article->update($kwargs);
    }

    /**
     * Delete an article
     * @param int $article_id Article ID
     * @return array Result with status and message
     */
    public function delete_article_ctr($article_id)
    {
        return $this->article->delete($article_id);
    }
}

?>

