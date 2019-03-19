<?php

namespace Model\DAO;

use Model\DAO\Conect;

/**
 * Interface para crud's 
 *
 * @author brunoblauzius
 */
abstract class AbstractDAO {
  
    private $con = null;
    
    public function getCon() {
        return $this->con;
    }
    
    public function __construct() {
        $this->con = Conect::conecta();
    }

    public function getDestroy(){
        Conect::destroy();
    }
    
    /**
     * @version 2.0
     */
    public function beginTransaction() {
        $this->con->beginTransaction();
    }

    /**
     * @version 2.0
     */
    public function commit() {
        $this->con->commit();
    }

    /**
     * @version 2.0
     */
    public function rollBack() {
        $this->con->rollBack();
    }
    
    /**
     * @version 2.0
     */
    public function lastInsertId() {
        return $this->con->lastInsertId();
    }

    /**
     * @version 2.0
     */
    public function errorCode() {
        return $this->con->errorCode();
    }


    abstract public function query(string $sql);
    abstract public function find(string $type = 'all', array $params = array() ) : array;
    abstract public function call(string $sql) : array ;
    abstract public function insert(array $array = NULL) : int;
    abstract public function update(array $array = null, string $primaryKey = 'id') : int;
    abstract public function delete(int $id = null) : bool;

}
