<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Conect
 *
 * @author brunoblauzius
 */
class Conect {
    
    /*const SERVER        = 'mysql';
    const HOST          = '192.168.1.3';
    const DATA_BASE     = 'teste';
    const USER          = 'desenv';
    const PASS          = 'desenv';*/
    
    
    const SERVER        = 'mysql';
    const HOST          = '200.155.11.2';
    const DATA_BASE     = 'BCIAdmin';
    const USER          = 'bci';
    const PASS          = '6Bq(V~xs0S)';
    
    private static $conn = null;

    public function __construct(){}

    /**
     * @version 0.1
     * @todo metodo de conexão compadrão de projeto singleton
     * @return PDO connect
     */
    public static function conecta(){
        try{
            if(is_null(self::$conn)){
                self::$conn = new PDO(self::SERVER . ':host=' . self::HOST . ';dbname='. self::DATA_BASE, self::USER, self::PASS);
            }
            return self::$conn;
        } catch (PDOException $ex) {
            echo $ex->getMessage();
        }
    }
    
    public static function destroy(){
        self::$conn = NULL;
    }
    
}

//Conect::conecta();
