<?php
include_once 'db_cred.php';

/**
 * Database Connection Class
 * @version 1.1
 */
if (!class_exists('db_connection')) {
    class db_connection
    {
        //properties
        public $db = null;
        public $results = null;

        //connect
        /**
         * Database connection
         * @return boolean
         **/
        function db_connect()
        {
            //connection
            $this->db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

            //test the connection
            if (mysqli_connect_errno()) {
                return false;
            } else {
                // Set charset
                mysqli_set_charset($this->db, DB_CHARSET);
                return true;
            }
        }

        function db_conn()
        {
            //connection
            $this->db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

            //test the connection
            if (mysqli_connect_errno()) {
                return false;
            } else {
                // Set charset
                mysqli_set_charset($this->db, DB_CHARSET);
                return $this->db;
            }
        }

        //execute a query for SELECT statements
        /**
         * Query the Database for SELECT statements
         * @param string $sqlQuery
         * @return boolean
         **/
        function db_query($sqlQuery)
        {
            if (!$this->db_connect()) {
                return false;
            } elseif ($this->db == null) {
                return false;
            }

            //run query 
            $this->results = mysqli_query($this->db, $sqlQuery);

            if ($this->results == false) {
                return false;
            } else {
                return true;
            }
        }

        //execute a query for INSERT, UPDATE, DELETE statements
        /**
         * Query the Database for INSERT, UPDATE, DELETE statements
         * @param string $sqlQuery
         * @return boolean
         **/
        function db_write_query($sqlQuery)
        {
            if (!$this->db_connect()) {
                return false;
            } elseif ($this->db == null) {
                return false;
            }

            //run query 
            $result = mysqli_query($this->db, $sqlQuery);

            if ($result == false) {
                return false;
            } else {
                return true;
            }
        }

        //fetch a single record
        /**
         * Get a single record
         * @param string $sql
         * @return array|false
         **/
        function db_fetch_one($sql)
        {
            // if executing query returns false
            if (!$this->db_query($sql)) {
                return false;
            }
            //return a record
            return mysqli_fetch_assoc($this->results);
        }

        //fetch all records
        /**
         * Get all records
         * @param string $sql
         * @return array|false
         **/
        function db_fetch_all($sql)
        {
            // if executing query returns false
            if (!$this->db_query($sql)) {
                return false;
            }
            //return all records
            return mysqli_fetch_all($this->results, MYSQLI_ASSOC);
        }

        //count data
        /**
         * Get count of records
         * @return int|false
         **/
        function db_count()
        {
            //check if result was set
            if ($this->results == null) {
                return false;
            } elseif ($this->results == false) {
                return false;
            }

            //return count
            return mysqli_num_rows($this->results);
        }

        /**
         * Get last insert ID
         * @return int|string
         **/
        function last_insert_id()
        {
            return mysqli_insert_id($this->db);
        }

        /**
         * Escape string for safe database queries
         * @param string $string
         * @return string
         **/
        function escape_string($string)
        {
            if (!$this->db_connect()) {
                return false;
            }
            return mysqli_real_escape_string($this->db, $string);
        }

        /**
         * Close database connection
         * @return boolean
         **/
        function db_close()
        {
            if ($this->db != null) {
                mysqli_close($this->db);
                $this->db = null;
                return true;
            }
            return false;
        }
    }
}

