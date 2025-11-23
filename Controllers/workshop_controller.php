<?php
/**
 * Workshop Controller
 * Creates an instance of the workshop class and runs the methods
 */

require_once __DIR__ . '/../Classes/workshop_class.php';

class workshop_controller
{
    private $workshop;

    public function __construct()
    {
        $this->workshop = new workshop_class();
    }

    /**
     * Add a new workshop
     * @param array $kwargs Workshop data
     * @return array Result with status and message
     */
    public function add_workshop_ctr($kwargs)
    {
        return $this->workshop->add($kwargs);
    }

    /**
     * Get all workshops
     * @return array All workshops
     */
    public function get_all_workshops_ctr()
    {
        return $this->workshop->get_all();
    }

    /**
     * Get a single workshop by ID
     * @param int $workshop_id Workshop ID
     * @return array|false Workshop data or false if not found
     */
    public function get_workshop_ctr($workshop_id)
    {
        return $this->workshop->get_one($workshop_id);
    }

    /**
     * Update a workshop
     * @param array $kwargs Workshop data
     * @return array Result with status and message
     */
    public function update_workshop_ctr($kwargs)
    {
        return $this->workshop->update($kwargs);
    }

    /**
     * Delete a workshop
     * @param int $workshop_id Workshop ID
     * @return array Result with status and message
     */
    public function delete_workshop_ctr($workshop_id)
    {
        return $this->workshop->delete($workshop_id);
    }
}
?>

