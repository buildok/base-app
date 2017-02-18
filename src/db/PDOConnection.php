<?php
namespace buildok\base\db;

use buildok\base\Config;
use buildok\base\exceptions\BaseAppException;

/**
 *
 */
class PDOConnection
{
    /**
     * Instance of this class
     * @var DataProvider
     */
    private static $_instance = null;

    /**
     * DB connection
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
     * @return DataProvider
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Prepare SQL query
     * @param  string $sql SQL query
     * @return PDOConnection This object
     */
    public function query($sql)
    {
        try {
            $this->stmt = $this->dbConnection->prepare($sql);
        } catch (\PDOException $e) {

            throw new BaseAppException($e->getMessage(), $e->getCode());
        }

        return $this;
    }

    /**
     * Execute prepared query
     * @param  array $params Query parameters
     * @return PDOConnection This object
     */
    public function exec($params = [])
    {
        if (!$this->stmt) {
            throw new BaseAppException('SQL query is not prepared', 'DB');
        }

        try {
            $this->stmt->execute($params);
        } catch (\PDOException $e) {

            throw new BaseAppException($e->getMessage(), $e->getCode());
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
            throw new BaseAppException('SQL query is not prepared', 'DB');
        }

        try {
            $data = $this->stmt->fetch($style);
            $this->stmt->closeCursor();
        } catch (\PDOException $e) {

            throw new BaseAppException($e->getMessage(), $e->getCode());
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
            throw new BaseAppException('SQL query is not prepared', 'DB');
        }

        try {
            $data = $this->stmt->fetchAll($style);
        } catch (\PDOException $e) {

            throw new BaseAppException($e->getMessage(), $e->getCode());
        }

        return $data;
    }

    public function call($params, $output = [])
    {

    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $cfg = (Config::getInstance())->dataProvider['db'];

        try {
            $options = [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ];

            $this->dbConnection = new \PDO($cfg['dsn'], $cfg['user'], $cfg['password'], $options);
        } catch(\PDOException $e) {

            throw new BaseAppException($e->getMessage(), $e->getCode());
        }
    }
}