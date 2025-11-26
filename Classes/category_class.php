<?php
/**
 * Category Class
 * Extends database connection and contains category methods
 */

require_once __DIR__ . '/../settings/db_class.php';

class category_class extends db_connection
{
    /**
     * Add a new category to the database
     * @param array $data Category data (cat_name)
     * @return array Result with status and message
     */
    public function add($data)
    {
        // Validate required fields
        if (empty($data['cat_name'])) {
            return [
                'status' => false,
                'message' => 'Category name is required.'
            ];
        }

        $cat_name = $this->escape_string(trim($data['cat_name']));

        // Check if category name already exists
        $check_sql = "SELECT cat_id FROM category WHERE cat_name = '$cat_name'";
        $existing = $this->db_fetch_one($check_sql);

        if ($existing) {
            return [
                'status' => false,
                'message' => 'Category name already exists. Please choose a different name.'
            ];
        }

        // Validate category name length
        if (strlen($cat_name) < 2) {
            return [
                'status' => false,
                'message' => 'Category name must be at least 2 characters long.'
            ];
        }

        if (strlen($cat_name) > 100) {
            return [
                'status' => false,
                'message' => 'Category name must not exceed 100 characters.'
            ];
        }

        // Insert category
        $sql = "INSERT INTO category (cat_name) VALUES ('$cat_name')";
        $result = $this->db_query($sql);

        if ($result) {
            return [
                'status' => true,
                'message' => 'Category added successfully.',
                'cat_id' => mysqli_insert_id($this->db)
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to add category. Please try again.'
            ];
        }
    }

    /**
     * Get all categories with product counts
     * @return array All categories with product counts
     */
    public function get_all()
    {
        $sql = "SELECT c.*, 
                COALESCE(COUNT(p.product_id), 0) as product_count
                FROM category c
                LEFT JOIN customer_products p ON c.cat_id = p.product_cat
                GROUP BY c.cat_id
                ORDER BY c.cat_name ASC";
        return $this->db_fetch_all($sql);
    }

    /**
     * Get a single category by ID
     * @param int $cat_id Category ID
     * @return array|false Category data or false if not found
     */
    public function get_one($cat_id)
    {
        $cat_id = (int)$cat_id;
        $sql = "SELECT * FROM category WHERE cat_id = $cat_id";
        return $this->db_fetch_one($sql);
    }

    /**
     * Get a single category by name
     * @param string $cat_name Category name
     * @return array|false Category data or false if not found
     */
    public function get_by_name($cat_name)
    {
        $cat_name = $this->escape_string(trim($cat_name));
        $sql = "SELECT * FROM category WHERE cat_name = '$cat_name'";
        return $this->db_fetch_one($sql);
    }

    /**
     * Update a category
     * @param array $data Category data (cat_id, cat_name)
     * @return array Result with status and message
     */
    public function update($data)
    {
        // Validate required fields
        if (empty($data['cat_id'])) {
            return [
                'status' => false,
                'message' => 'Category ID is required.'
            ];
        }

        if (empty($data['cat_name'])) {
            return [
                'status' => false,
                'message' => 'Category name is required.'
            ];
        }

        $cat_id = (int)$data['cat_id'];
        $cat_name = $this->escape_string(trim($data['cat_name']));

        // Check if category exists
        $existing = $this->get_one($cat_id);
        if (!$existing) {
            return [
                'status' => false,
                'message' => 'Category not found.'
            ];
        }

        // Check if another category with the same name exists (excluding current category)
        $check_sql = "SELECT cat_id FROM category WHERE cat_name = '$cat_name' AND cat_id != $cat_id";
        $duplicate = $this->db_fetch_one($check_sql);

        if ($duplicate) {
            return [
                'status' => false,
                'message' => 'Category name already exists. Please choose a different name.'
            ];
        }

        // Validate category name length
        if (strlen($cat_name) < 2) {
            return [
                'status' => false,
                'message' => 'Category name must be at least 2 characters long.'
            ];
        }

        if (strlen($cat_name) > 100) {
            return [
                'status' => false,
                'message' => 'Category name must not exceed 100 characters.'
            ];
        }

        // Update category
        $sql = "UPDATE category SET cat_name = '$cat_name' WHERE cat_id = $cat_id";
        $result = $this->db_query($sql);

        if ($result) {
            return [
                'status' => true,
                'message' => 'Category updated successfully.'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to update category. Please try again.'
            ];
        }
    }

    /**
     * Delete a category
     * @param int $cat_id Category ID
     * @return array Result with status and message
     */
    public function delete($cat_id)
    {
        $cat_id = (int)$cat_id;

        // Check if category exists
        $existing = $this->get_one($cat_id);
        if (!$existing) {
            return [
                'status' => false,
                'message' => 'Category not found.'
            ];
        }

        // Check if category is being used by products
        $check_products = "SELECT COUNT(*) as count FROM customer_products WHERE product_cat = $cat_id";
        $product_count = $this->db_fetch_one($check_products);
        
        if ($product_count && $product_count['count'] > 0) {
            return [
                'status' => false,
                'message' => 'Cannot delete category. It is being used by ' . $product_count['count'] . ' product(s).'
            ];
        }

        // Delete category
        $sql = "DELETE FROM category WHERE cat_id = $cat_id";
        $result = $this->db_query($sql);

        if ($result) {
            return [
                'status' => true,
                'message' => 'Category deleted successfully.'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to delete category. Please try again.'
            ];
        }
    }
}

?>

