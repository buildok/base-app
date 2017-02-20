<?php
namespace buildok\base\db;

use buildok\base\exceptions\AppException;

/**
 *
 */
class PDOConnection
{
    /**
     * Instance of this class
     * @var PDOConnection
     */
    private static $_instance = null;

    /**
     * DB connection object
     * @var \PDO
     */
    private $dbConnection;

    /**
     * Prepared SQL query
     * @var \PDOStatement
     */
    private $stmt = null;

    /**
     * Get object of this class
     * @return PDOConnection
     */
    public static function getInstance($dsn, $user, $password = '')
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($dsn, $user, $password);
        }

        return self::$_instance;
    }

    /**
     * Prepare SQL query
     * @param  string $sql SQL query
     * @return PDOConnection self
     */
    public function query($sql)
    {
        try {
            $this->stmt = $this->dbConnection->prepare($sql);
        } catch (\PDOException $e) {

            throw new AppException($e->getMessage(), $e->getCode());
        }

        return $this;
    }

    /**
     * Execute prepared query
     * @param  array $params Query parameters
     * @return PDOConnection self
     */
    public function exec($params = [])
    {
        if (!$this->stmt) {
            throw new AppException('SQL query is not prepared', 'DB');
        }

        try {
            $this->stmt->execute($params);
        } catch (\PDOException $e) {

            throw new AppException($e->getMessage(), $e->getCode());
        }

        return $this;
    }

    /**
     * Fetch one row from dataset
     * @param  int $style Fetch style
     * @return array
     */
    public function one($style = \PDO::FETCH_ASSOC)
    {
        if (!$this->stmt) {
            throw new AppException('SQL query is not prepared', 'DB');
        }

        try {
            $data = $this->stmt->fetch($style);
            $this->stmt->closeCursor();
        } catch (\PDOException $e) {

            throw new AppException($e->getMessage(), $e->getCode());
        }

        return $data;
    }

    /**
     * Fetch all rows from dataset
     * @param  int $style Fetch style
     * @return array
     */
    public function all($style = \PDO::FETCH_ASSOC)
    {
        if (!$this->stmt) {
            throw new AppException('SQL query is not prepared', 'DB');
        }

        try {
            $data = $this->stmt->fetchAll($style);
        } catch (\PDOException $e) {

            throw new AppException($e->getMessage(), $e->getCode());
        }

        return $data;
    }

    public function call($params, $output = [])
    {

    }

    /**
     * Constructor
     */
    private function __construct($dsn, $user, $password)
    {
        try {
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ];

            $this->dbConnection = new \PDO($dsn, $user, $password, $options);
        } catch(\PDOException $e) {

            throw new AppException('PDOConnection:'.$e->getMessage(), $e->getCode());
        }
    }
}