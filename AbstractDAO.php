<?php

namespace DAO;


use DAO\Conect as Conect;

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
    
    
    abstract public function update( array $array = null, $primaryKey = 'id' );
    abstract public function delete( $id = null );
    abstract public function insert( array $array = NULL );
    abstract public function find($type = 'all', array $params = array() );
    abstract public function query( $sql );
    abstract public function call( $sql );
    
}
