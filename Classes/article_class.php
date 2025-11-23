<?php
/**
 * Article Class
 * Extends database connection and contains article methods
 */

require_once __DIR__ . '/../settings/db_class.php';

class article_class extends db_connection
{
    /**
     * Add a new article to the database
     * @param array $data Article data
     * @return array Result with status and message
     */
    public function add($data)
    {
        // Validate required fields
        if (empty($data['article_title'])) {
            return [
                'status' => false,
                'message' => 'Article title is required.'
            ];
        }

        if (empty($data['article_author'])) {
            return [
                'status' => false,
                'message' => 'Article author is required.'
            ];
        }

        if (empty($data['article_cat']) || $data['article_cat'] <= 0) {
            return [
                'status' => false,
                'message' => 'Article category is required.'
            ];
        }

        $article_title = $this->escape_string(trim($data['article_title']));
        $article_author = $this->escape_string(trim($data['article_author']));
        $article_cat = (int)$data['article_cat'];
        // article_body is now LONGBLOB and will be handled separately via PDF upload

        // Validate title length
        if (strlen($article_title) < 3) {
            return [
                'status' => false,
                'message' => 'Article title must be at least 3 characters long.'
            ];
        }

        if (strlen($article_title) > 200) {
            return [
                'status' => false,
                'message' => 'Article title must not exceed 200 characters.'
            ];
        }

        // Validate author length
        if (strlen($article_author) < 2) {
            return [
                'status' => false,
                'message' => 'Article author must be at least 2 characters long.'
            ];
        }

        if (strlen($article_author) > 100) {
            return [
                'status' => false,
                'message' => 'Article author must not exceed 100 characters.'
            ];
        }

        // Check if category exists (assuming article_cat references category table)
        $check_cat = "SELECT cat_id FROM category WHERE cat_id = $article_cat";
        if (!$this->db_fetch_one($check_cat)) {
            return [
                'status' => false,
                'message' => 'Selected category does not exist.'
            ];
        }

        // Ensure database connection
        if (!$this->db_connect()) {
            return [
                'status' => false,
                'message' => 'Database connection failed.'
            ];
        }

        // Insert article (article_body is LONGBLOB, handled separately via PDF upload)
        // For now, insert without article_body - it will be added via upload_article_pdf_action.php
        $sql = "INSERT INTO articles (article_title, article_author, article_cat) 
                VALUES ('$article_title', '$article_author', $article_cat)";
        $result = $this->db_query($sql);

        if ($result) {
            return [
                'status' => true,
                'message' => 'Article added successfully.',
                'article_id' => mysqli_insert_id($this->db)
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to add article. Please try again.'
            ];
        }
    }

    /**
     * Get all articles with category information
     * @return array All articles (without article_body binary data for list view)
     */
    public function get_all()
    {
        $sql = "SELECT a.article_id, a.article_title, a.article_author, a.article_cat, a.date_added, 
                CASE WHEN a.article_body IS NOT NULL AND LENGTH(a.article_body) > 0 THEN 1 ELSE 0 END as has_pdf,
                COALESCE(COUNT(av.view_id), 0) as article_views,
                c.cat_name 
                FROM articles a
                LEFT JOIN category c ON a.article_cat = c.cat_id
                LEFT JOIN article_views av ON a.article_id = av.article_id
                GROUP BY a.article_id, a.article_title, a.article_author, a.article_cat, a.date_added, c.cat_name
                ORDER BY a.date_added DESC";
        return $this->db_fetch_all($sql);
    }

    /**
     * Get a single article by ID
     * @param int $article_id Article ID
     * @param bool $include_pdf Whether to include PDF binary data
     * @return array|false Article data or false if not found
     */
    public function get_one($article_id, $include_pdf = false)
    {
        $article_id = (int)$article_id;
        
        if ($include_pdf) {
            // Include PDF binary data
            $sql = "SELECT a.*, c.cat_name 
                    FROM articles a
                    LEFT JOIN category c ON a.article_cat = c.cat_id
                    WHERE a.article_id = $article_id";
        } else {
            // Exclude PDF binary data for performance
            $sql = "SELECT a.article_id, a.article_title, a.article_author, a.article_cat, a.date_added,
                    CASE WHEN a.article_body IS NOT NULL AND LENGTH(a.article_body) > 0 THEN 1 ELSE 0 END as has_pdf,
                    COALESCE(COUNT(av.view_id), 0) as article_views,
                    c.cat_name 
                    FROM articles a
                    LEFT JOIN category c ON a.article_cat = c.cat_id
                    LEFT JOIN article_views av ON a.article_id = av.article_id
                    WHERE a.article_id = $article_id
                    GROUP BY a.article_id, a.article_title, a.article_author, a.article_cat, a.date_added, c.cat_name";
        }
        
        return $this->db_fetch_one($sql);
    }
    
    /**
     * Get article PDF binary data
     * @param int $article_id Article ID
     * @return string|false PDF binary data or false if not found
     */
    public function get_article_pdf($article_id)
    {
        $article_id = (int)$article_id;
        $sql = "SELECT article_body FROM articles WHERE article_id = $article_id";
        $result = $this->db_fetch_one($sql);
        
        if ($result && !empty($result['article_body'])) {
            return $result['article_body'];
        }
        
        return false;
    }

    /**
     * Update an article
     * @param array $data Article data
     * @return array Result with status and message
     */
    public function update($data)
    {
        // Validate required fields
        if (empty($data['article_id']) || $data['article_id'] <= 0) {
            return [
                'status' => false,
                'message' => 'Article ID is required.'
            ];
        }

        if (empty($data['article_title'])) {
            return [
                'status' => false,
                'message' => 'Article title is required.'
            ];
        }

        if (empty($data['article_author'])) {
            return [
                'status' => false,
                'message' => 'Article author is required.'
            ];
        }

        if (empty($data['article_cat']) || $data['article_cat'] <= 0) {
            return [
                'status' => false,
                'message' => 'Article category is required.'
            ];
        }

        $article_id = (int)$data['article_id'];
        $article_title = $this->escape_string(trim($data['article_title']));
        $article_author = $this->escape_string(trim($data['article_author']));
        $article_cat = (int)$data['article_cat'];
        // article_body is now LONGBLOB, so we handle it differently
        $article_body = isset($data['article_body']) ? $data['article_body'] : null;

        // Check if article exists
        $existing = $this->get_one($article_id);
        if (!$existing) {
            return [
                'status' => false,
                'message' => 'Article not found.'
            ];
        }

        // Validate title length
        if (strlen($article_title) < 3) {
            return [
                'status' => false,
                'message' => 'Article title must be at least 3 characters long.'
            ];
        }

        if (strlen($article_title) > 200) {
            return [
                'status' => false,
                'message' => 'Article title must not exceed 200 characters.'
            ];
        }

        // Validate author length
        if (strlen($article_author) < 2) {
            return [
                'status' => false,
                'message' => 'Article author must be at least 2 characters long.'
            ];
        }

        if (strlen($article_author) > 100) {
            return [
                'status' => false,
                'message' => 'Article author must not exceed 100 characters.'
            ];
        }

        // Check if category exists
        $check_cat = "SELECT cat_id FROM category WHERE cat_id = $article_cat";
        if (!$this->db_fetch_one($check_cat)) {
            return [
                'status' => false,
                'message' => 'Selected category does not exist.'
            ];
        }

        // Update article (article_body is LONGBLOB, handled separately if provided)
        if ($article_body !== null) {
            // Use prepared statement for binary data
            $stmt = mysqli_prepare($this->db, "UPDATE articles SET article_title = ?, article_author = ?, article_cat = ?, article_body = ? WHERE article_id = ?");
            if ($stmt) {
                $null = null;
                mysqli_stmt_bind_param($stmt, "ssibi", $article_title, $article_author, $article_cat, $null, $article_id);
                mysqli_stmt_send_long_data($stmt, 3, $article_body);
                $result = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            } else {
                return [
                    'status' => false,
                    'message' => 'Failed to prepare update statement.'
                ];
            }
        } else {
            // Update without article_body
            $sql = "UPDATE articles 
                    SET article_title = '$article_title', 
                        article_author = '$article_author', 
                        article_cat = $article_cat 
                    WHERE article_id = $article_id";
            $result = $this->db_query($sql);
        }

        if ($result) {
            return [
                'status' => true,
                'message' => 'Article updated successfully.'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to update article. Please try again.'
            ];
        }
    }

    /**
     * Delete an article
     * @param int $article_id Article ID
     * @return array Result with status and message
     */
    public function delete($article_id)
    {
        $article_id = (int)$article_id;

        // Check if article exists
        $existing = $this->get_one($article_id);
        if (!$existing) {
            return [
                'status' => false,
                'message' => 'Article not found.'
            ];
        }

        // Delete article
        $sql = "DELETE FROM articles WHERE article_id = $article_id";
        $result = $this->db_query($sql);

        if ($result) {
            return [
                'status' => true,
                'message' => 'Article deleted successfully.'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to delete article. Please try again.'
            ];
        }
    }
}

?>

