<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AbstractDAO
 *
 * @author brunoblauzius
 */
abstract class AbstractDAO {
  
    
    abstract public function genericInsert( array $array = NULL );
    abstract public function genericUpdate( array $array = null, $primaryKey = 'id' );
    abstract public function genericDelete( $id = null, $primaryKey = null );
    abstract public function genericExecuteCall( $callabeFunction = null, array $array = NULL);
    
}
