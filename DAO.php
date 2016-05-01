<?php

require_once 'autoload.php';

namespace DAO;


use DAO\AbstractDAO as AbstractDAO;

/**
 * Classe que realiza CRUD'S genericos no banco de dados
 *
 * @author brunoblauzius
 */
class DAO extends AbstractDAO {

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
    protected $valueKeys = null;

    /**
     * @version 1.10
     * @todo atributo que gera chave preimaria com clausula where para update
     * @var string; 
     */
    protected $valueKeysId = null;

    /**
     * @version 0.1
     * @todo nome da tabela a ser usada
     * @var string 
     */
    public $useTable = null;

    /**
     * @todo model name
     * @var string
     */
    public $name = null;

    /**
     * @todo nome da chave primaria
     * @var String
     */
    public $primaryKey = 'id';

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
    

    /**
     * @version 1.10
     * @todo metodo que faz a inserção generica no banco de dados 
     * @param array $array preferenciamente o post ja validado
     * @return int id da tranxsação
     */
    public final function insert(array $array = NULL) {
        try {

            $values = $this->prepareKeyValuesForExecute($array);

            $sql = "INSERT INTO {$this->useTable} ( " . implode(',', array_keys($array)) . " ) VALUES( " . implode(',', array_keys($values)) . " )";

            $stmt = $this->getCon()->prepare($sql);

            $this->getCon()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $stmt->execute($values);

            $id = (int) $this->getCon()->lastInsertId();

            return $id;
        } catch (\PDOException $ex) {
            $this->getDestroy();
            throw $ex;
        }
    }

    /**
     * 
     * @todo metodo que persiste o update na data base
     * @version 1.10
     * @param Arrays $array
     * @param String $primaryKey
     * @return Boolean 
     * @throws \PDOException
     */
    public final function update(array $array = null, $primaryKey = 'id') {
        try {
            if (!empty($array) && is_array($array)) {

                // verifico se minha chave procurada existe
                $this->checkArrayExists($primaryKey, $array);

                $this->prepareKeyValuesUpdateDelete($array, $primaryKey);

                $valuesExecute = $this->prepareKeyValuesForExecute($array);

                echo $sql = " UPDATE {$this->useTable} SET " . implode(',', $this->valueKeys) . $this->valueKeysId;

                $stmt = $this->getCon()->prepare($sql);

                $this->getCon()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

                $stmt->execute($valuesExecute);

                $retorno = $stmt->rowCount();

                return $retorno;
            } else {
                throw new \PDOException(self::ARRAY_NULL);
            }
        } catch (\PDOException $ex) {
            $this->getDestroy();
            throw $ex;
        }
    }

    /**
     * @version 1.10
     * @todo metodo generico para deletar um indice na tabela
     * @param  type $id
     * @param  type $primaryKey
     * @return boolean
     * @throws Exception
     */
    public final function delete($id = null) {
        try {

            $sql = "DELETE FROM {$this->useTable} WHERE {$this->primaryKey} = :{$this->primaryKey}";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->bindParam(':' . $this->primaryKey, $id, \PDO::PARAM_INT);
            $this->getCon()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $stmt->execute();
            return TRUE;
        } catch (\PDOException $ex) {
            $this->getDestroy();
            throw $ex;
        }
    }

    /**
     * REFORMULAR O FIND DESTE MÉTODO
     * @param type $type
     * @param type $condicoes
     * @param type $fields
     * @param type $limit
     * @param string $order
     * @return type
     * @throws \DAO\Exception
     * 
     * @uses 
     * 
     * $retorno = $model->find('all', array(
            'conditions' => array( 'Pessoa.nome LIKE' => 'bruno%' ),
            'fields'     => array('Pessoa.*', 'Empresa.nome as nome_fantasia'),
            'order'      => array(' Usuario.nome  DESC'),
            'limit'      => array(0,23),
     *          
     *      #OPICIONAL
     * 
            'JOINS'      => array(
                                array(
                                    'join'  => 'LEFT JOIN',
                                    'table' => 'sistemasEmpresas',
                                    'as'    => 'Empresa',
                                    'id'    => 'idSistemasEmpresas',
                                    'parent_id' => 'idSistemasEmpresas',
                                ),
                                array(
                                    'join'  => 'LEFT JOIN',
                                    'table' => 'sistemasPessoas',
                                    'as'    => 'Pessoa',
                                    'id'    => 'idSistemasPessoas',
                                    'parent_id' => 'idPessoas',
                                )
                            ) 
        ));
     * 
     */
    public function find($type = 'all', array $params = array() ){
        try {
            
            $condition = NULL;
            $fields    = '*';
            $order     = NULL;
            $limit     = NULL;
            $inner     = NULL;
            
            if( $type == 'first' ){
                $limit = ' LIMIT 1 ';
            }else if( $type == 'all' && isset ($params['limit']) && is_array($params['limit'])){
                $limit = ' LIMIT ' . join(',', $params['limit']);
            }
            else if( $type == 'all' && isset ($params['limit']) && !is_array($params['limit'])){
                $limit = ' LIMIT ' . $params['limit'];
            }
            if( isset($params['conditions']) && !empty($params['conditions']) ){
                 if (is_array($params['conditions'])) {
                        foreach ($params['conditions'] as $key => $value) {

                            if (is_numeric($value) && !is_null($value)) {
                                if( stripos($key, '!=') !== false )
                                {
                                    $condition[] = " {$key} {$value} ";
                                } else {
                                    $condition[] = "{$key} = {$value} ";
                                }
                            } 
                            else if (is_array($value) && !empty($value)) 
                            {
                                $value = join(',', $value);
                                $condition[] = "{$key} IN ( {$value} ) ";
                            }
                            else if( stripos($key, ' LIKE') !== false )
                            {
                                $condition[] = " {$key} '{$value}' ";
                            }
                            else if( stripos($key, '!=') !== false )
                            {
                                $condition[] = " {$key} {$value} ";
                            }
                            else {
                                $condition[] = "{$key} = '$value' ";
                            }
                        }
                        $condition = " WHERE " . join(' AND ', $condition);
                    } 

            }
            if( isset($params['fields']) && !empty($params['fields']) )
            {
                $fields = join(',', $params['fields']);
            }
            if( isset($params['order']) && !empty($params['order']) )
            {
                $order = " ORDER BY " . array_shift($params['order']);
            }
            if( !empty($params['JOINS']) && is_array($params['JOINS']) )
            {
                foreach ($params['JOINS'] as $rows){
                    $inner[] = $rows['join'] .' '.$rows['table'].' as '.$rows['as']. ' on '. $rows['as'].'.'.$rows['id']. ' = ' .ucfirst($this->name).'.'.$rows['parent_id'] ; 
                }
                $inner = join(' ', $inner);
            }

            $sql = "SELECT {$fields} FROM {$this->useTable} as {$this->name} " .$inner. $condition . $order . $limit .';';
            return $this->query($sql);
            
        } catch (\PDOException $ex) {
            throw $ex;
        }
    }
    

    public final function query($sql) {
        try {
            $retorno = NULL;

            $stmt = $this->getCon()->prepare($sql);
            $this->getCon()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, TRUE);
            $this->getCon()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $stmt->execute();

            if (stripos($sql, 'SELECT') !== FALSE) {
                $retorno = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                if (!empty($retorno)) {
                    $retorno = $this->findListModel($retorno);
                } else {
                    $retorno = NULL;
                }
            } else if (stripos($sql, 'UPDATE') !== FALSE) {
                $retorno = $stmt->rowCount();
            } else if (stripos($sql, 'INSERT INTO') !== FALSE) {
                $retorno = $this->getCon()->lastInsertId();
            }

            return $retorno;
        } catch (\PDOException $ex) {
            $this->getDestroy();
            throw $ex;
        }
    }

    public final function call($sql) {
        try {
            $stmt = $this->getCon()->prepare($sql);

            $this->getCon()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, TRUE);
            $this->getCon()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $stmt->execute();

            $retorno = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $retorno;
        } catch (\Exception $ex) {
            $this->getDestroy();
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
        if(is_array($list) && !empty($list)){
            
            foreach ($list as $row) {
                if (!is_numeric(key($row))) {
                    $array = $row;
                }
                if (!is_null($this->name)) {
                    $arrayMaster[][$this->name] = $array;
                } else {
                    $arrayMaster[] = $array;
                }
            }
            
        }
        return $arrayMaster;
    }

    /**
     * @metodo que gera um cast no array e identifica o meu model
     * @param array $list
     */
    public function inList(array $list = array()) {
        $array = array();
        $arrayMaster = array();
        foreach ($list as $row) {
            if (!is_numeric(key($row))) {
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
    private function checkArrayExists($key = null, array $array = array()) {
        try {
            if (in_array($key, array_keys($array))) {
                return TRUE;
            } else {
                throw new \Exception(self::KEY_IN_ARRAY_NO_EXISTS);
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * @version 1.10
     * @param array $array
     * @param string $primarykey
     * @throws Exception
     */
    private final function prepareKeyValuesUpdateDelete(array $array = null) {
        try {
            if (!empty($array) && is_array($array)) {
                foreach ($array as $key => $value) {
                    if ($key == $this->primarykey) {
                        $this->valueKeysId = " WHERE {$this->primarykey} = :{$this->primarykey}";
                    } else {
                        $this->valueKeys[] = "{$key} = :{$key}";
                    }
                }
            } else {
                throw new \Exception(self::ARRAY_NULL);
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * @version 1.10
     * @todo metodo que gera chaves para o modelo PDO
     * @param array $array
     * @return array valores das keys alterados
     * @throws Exception
     */
    private final function prepareKeyValuesForExecute(array $array = null) {
        try {
            if (!empty($array) && is_array($array)) {
                $values = array();
                foreach ($array as $key => $value) {
                    $values[':' . $key] = $value;
                }
                return $values;
            } else {
                throw new \Exception(self::ARRAY_NULL);
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function __destruct() {
        $this->getDestroy();
    }

}
