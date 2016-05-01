<?php

namespace DAO;


/**
 * Classe de conexao com o banco de dados *
 * @author brunoblauzius
 */
class Conect {
    
    
    const SERVER        = 'mysql';
    const HOST          = 'localhost';
    const DATA_BASE     = 'site_cars';
    const USER          = 'root';
    const PASS          = '';
    
    
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

