<?php

namespace DAO;


/**
 * Classe de conexao com o banco de dados *
 * @author brunoblauzius
 */
class Conect {
    
    /*
    const SERVER        = 'mysql';
    const HOST          = '192.168.1.3';
    const DATA_BASE     = 'teste';
    const USER          = 'desenv';
    const PASS          = 'desenv';
     */
    
    
    const SERVER        = 'mysql';
    const HOST          = '200.155.11.2';
    const DATA_BASE     = 'BCIAdmin';
    const USER          = 'bci';
    const PASS          = '6Bq(V~xs0S)';
    
    private static $conn = null;

    public function __construct(){}

    /**
     * @version 0.1
     * @todo metodo de conexão design-patterns singleton
     * @return PDO connect
     */
    public static function conecta()
    {
        try{
            if(is_null(self::$conn))
            {
                self::$conn = new \PDO(self::SERVER . ':host=' . self::HOST . ';dbname='. self::DATA_BASE, self::USER, self::PASS);
            }
            return self::$conn;
        } 
        catch (\PDOException $ex) 
        {
            throw $ex;
        }
    }
    
    public static function destroy()
    {
        self::$conn = NULL;
    }
    
}

