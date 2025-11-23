<?php
/**
 * Category Controller
 * Creates an instance of the category class and runs the methods
 */

require_once __DIR__ . '/../Classes/category_class.php';

class category_controller
{
    private $category;

    public function __construct()
    {
        $this->category = new category_class();
    }

    /**
     * Add a new category
     * @param array $kwargs Category data (cat_name)
     * @return array Result with status and message
     */
    public function add_category_ctr($kwargs)
    {
        return $this->category->add($kwargs);
    }

    /**
     * Get all categories
     * @return array All categories
     */
    public function get_all_categories_ctr()
    {
        return $this->category->get_all();
    }

    /**
     * Get a single category by ID
     * @param int $cat_id Category ID
     * @return array|false Category data or false if not found
     */
    public function get_category_ctr($cat_id)
    {
        return $this->category->get_one($cat_id);
    }

    /**
     * Update a category
     * @param array $kwargs Category data (cat_id, cat_name)
     * @return array Result with status and message
     */
    public function update_category_ctr($kwargs)
    {
        return $this->category->update($kwargs);
    }

    /**
     * Delete a category
     * @param int $cat_id Category ID
     * @return array Result with status and message
     */
    public function delete_category_ctr($cat_id)
    {
        return $this->category->delete($cat_id);
    }
}

?>

