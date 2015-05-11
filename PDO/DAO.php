<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Conect.php';
require_once 'AbstractDAO.php';
/**
 * Description of Service
 *
 * @author brunoblauzius
 */
class DAO extends AbstractDAO{
    
    
    /**
     * @version 0.1
     * @var type 
     */
    private $con = null;
    
    /**
     * @version 1.10
     * @todo atributo que gera arrays para cadastro de valores no update
     * @var array; 
     */
    protected  $valueKeys   = null;
    /**
     * @version 1.10
     * @todo atributo que gera chave preimaria com clausula where para update
     * @var string; 
     */
    protected  $valueKeysId = null;

    /**
     * @version 0.1
     * @todo nome da tabela a ser usada
     * @var string 
     */
    public $useTable = null;
    
    
    public $name = null;
    
    
    /**
     * @version 1.10
     * @todo constante para numerar o tipo do valor
     */
    const ARRAY_NULL = 'O array esta vazio ou não existe!';
    const KEY_IN_ARRAY_NO_EXISTS = 'A chave procurada no array não existe!';
    
    
    /**
     * @version 0.1
     * @todo metodo construtor usado para gerar a conexão com o banco de dados
     */
    public function __construct() {
        $this->con = Conect::conecta();
    }

    
    /**
     * @version 1.10
     * @todo metodo que faz a inserção generica no banco de dados 
     * @param array $array preferenciamente o post ja validado
     * @return int id da tranxsação
     */
    public final function genericInsert( array $array = NULL ){
        try{
            // inicio minha transação
            $this->con->beginTransaction();
                // preparo meu array com chaves e valores para meu insert no model PDO
                $values = $this->prepareKeyValues($array);
                //construo meu sql PDO
                $sql = "INSERT INTO {$this->useTable} ( ".  implode(',', array_keys($array))." ) VALUES( ".  implode(',', array_keys($values))." )";
                $stmt = $this->con->prepare($sql);
                $this->con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
                $stmt->execute($values);
                $lastId = $this->con->lastInsertId();
            //se houve eu aplico o commit e retorno o ultimo id
            $this->con->commit();
            //recupero o ultimo id inserido
            return (int) $lastId;
            
        } catch (PDOException $ex) {
            $this->con->rollBack();
            throw  $ex;
        }
    }
    
    
    
    public final function genericUpdate( array $array = null, $primaryKey = 'id' ){
        try{
            if( !empty($array) && is_array($array) ){
                // verifico se minha chave procurada existe
                $this->checkArrayExists( $primaryKey, $array );
                $this->prepareKeyValuesUpdateDelete($array);
                $valuesExecute = $this->prepareKeyValues($array);
                $this->con->beginTransaction();
                    $sql = " UPDATE {$this->useTable} SET ".  implode(',', $this->valueKeys). $this->valueKeysId;
                    $stmt = $this->con->prepare($sql);
                    $this->con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
                    $stmt->execute($valuesExecute);
                $this->con->commit();
                return $stmt->rowCount();
            } else {
                throw new PDOException( self::ARRAY_NULL );
            } 
        } catch (PDOException $ex) {
            $this->con->rollBack();
            throw $ex;
        }
    }
    
    /**
     * @version 1.10
     * @todo metodo generico para deletar um indice na tabela
     * @param type $id
     * @param type $primaryKey
     * @return boolean
     * @throws Exception
     */
    public final function genericDelete( $id = null, $primaryKey = null ) {
        try{
            $this->con->beginTransaction();
                $sql = "DELETE FROM {$this->useTable} WHERE {$primaryKey} = :{$primaryKey}";
                $stmt = $this->con->prepare($sql);
                $stmt->bindParam( ':'.$primaryKey , $id, PDO::PARAM_INT );
                $this->con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
                $stmt->execute();
                $this->con->errorCode();
            $this->con->commit();
            return TRUE;
        } catch (PDOException $ex) {
            $this->con->rollBack();
            throw $ex;
        }
    }
    
    /**
     * @version 1.10
     * @todo metodo generico para executar metodos call
     * OBS. AINDA NÃO FOI TESTADO
     * @param type $callabeFunction
     * @param array $array
     * @return type
     * @throws PDOException
     */
    public final function genericExecuteCall( $callabeFunction = null, array $array = NULL){
        try{
                $valuesExecute = $this->prepareKeyValues($array);
                $sql = "CALL {$callabeFunction}( " . implode( ',', array_keys($valuesExecute) ) . " );";
                $stmt = $this->con->prepare( $sql );
                $this->con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
                $stmt->execute( $valuesExecute );
                //trabalhar a exception pelo erro gerado
                $this->con->errorCode();
            return TRUE;
        } catch (PDOException $ex) {
            throw $ex;
        }
    }


    public final function find( $type = 'all', $condicoes = null, $fields = null, $limit = null, $order = null ){
        try{
            $condition = null;
            if( !is_null( $type ) ) {
                if( strtolower( $type ) == 'first' ) {
                    $limit = ' LIMIT 1 ';
                } else if( strtolower( $type ) == 'all') {
                    $limit = '';
                } else if(is_array($limit)){
                    $limit = " LIMIT ".  join( ',', $limit );
                }
            } else {
                $limit = '';
            }
            
            //fields
            if(is_array($fields)) {
                $fields = join(',', $fields);
            } else {
                $fields = '*';
            }
            
            if( !is_null($order)) {
                $order = ' ORDER BY ' . join(', ', $order);
            }
            
            if( !is_null( $condicoes ) ) {
                if( is_array( $condicoes )) {
                    $condition = array();
                    foreach( $condicoes as $key => $value ) {
                        
                        if( is_numeric($value) && !is_null($value) ) {
                            $condition[] = "{$key} = {$value} ";
                        } else {
                            $condition[] = "{$key} = '$value' "; 
                        } 
                        
                    }
                    $condition = " WHERE ". join ( ' AND ', $condition );
                } else {
                    $condition =  " WHERE ".$condicoes ;
                }
            }
            
            
                
                $sql = "SELECT {$fields} FROM {$this->useTable} ". $condition . $order. $limit;
                $stmt = $this->con->prepare($sql);
                $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $result = $this->findListModel($result);
                return $result;
      
        } catch (Exception $ex) {
            $this->con->rollBack();
            throw $ex;
        }
    }
    
    
    public function query( $sql ){
        try{
            $retorno = NULL;
            //$this->con->beginTransaction();
                $stmt = $this->con->prepare($sql);
                $this->con->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, TRUE);
                $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
                $stmt->execute();
            //$this->con->commit();
                        
            if (stripos($sql,'SELECT') !== FALSE) {
                $retorno = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if( !empty($retorno) ) {
                    $retorno = $this->findListModel($retorno);
                } else {
                    $retorno = NULL;
                }
            } else if (stripos($sql,'UPDATE') !== FALSE) {
                $retorno = $stmt->rowCount();  
            } else if( stripos($sql,'INSERT INTO') !== FALSE ){
                $retorno = $this->con->lastInsertId();
            }
            
            Conect::destroy();
            return $retorno;
        } catch (PDOException $ex) {
            Conect::destroy();
            throw $ex;
        }
    }
    
    
    /**
     * @metodo que gera um cast no array e identifica o meu model
     * @param array $list
     */
    public function findListModel( array $list = array() ) {
        $array = array();
        $arrayMaster = array();
        foreach( $list as $row ) {       
            if( !is_numeric( key( $row ) )) {
                $array = $row;
            }
            $arrayMaster[][$this->name] = $array;  
        }
        return $arrayMaster;
    }
    
    
    /**
     * @metodo que gera um cast no array e identifica o meu model
     * @param array $list
     */
    public function inList( array $list = array() ) {
        $array = array();
        $arrayMaster = array();
        foreach( $list as $row ) {       
            if( !is_numeric( key( $row ) )) {
                $array = $row;
            }
            $arrayMaster[] = array_shift($array);  
        }
        return $arrayMaster;
    }
    
    
    /**
     * @metodo que verifica se existe id no array
     * @param string $key
     * @param array $array
     * @return boolean
     * @throws Exception
     */
    private function checkArrayExists( $key = null, array $array = array() ){
        try{
             if( in_array( $key, array_keys($array)) ) {
               return TRUE;
            }else {
                throw new Exception(self::KEY_IN_ARRAY_NO_EXISTS);
            }
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * @version 1.10
     * @param array $array
     * @param string $primarykey
     * @throws Exception
     */
    private function prepareKeyValuesUpdateDelete( array $array = null, $primarykey = 'id' ){
        try{
            if( !empty($array) && is_array($array) ){
                foreach ($array as $key => $value) {
                    if( $key == $primarykey ){
                        $this->valueKeysId = " WHERE {$key} = :{$key}";
                    } else {
                        $this->valueKeys[] = "{$key} = :{$key}";
                    }
                }
            } else {
                throw new Exception( self::ARRAY_NULL );
            } 
        } catch (Exception $ex) {
            throw  $ex;
        }
    }

    /**
     * @version 1.10
     * @todo metodo que gera chaves para o modelo PDO
     * @param array $array
     * @return array valores das keys alterados
     * @throws Exception
     */
    private function prepareKeyValues( array $array = null ){
        try{
            if( !empty($array) && is_array($array) ){
                $values = array();
                foreach ($array as $key => $value) {
                    $values[':' . $key] = $value;
                }
                return $values;
            } else {
                throw new Exception( self::ARRAY_NULL );
            } 
        } catch (Exception $ex) {
            throw  $ex;
        }
    }
    
    public function __destruct() {
        Conect::destroy();
    }

}


