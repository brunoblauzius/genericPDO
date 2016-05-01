<?php

try {
    
include_once "DAO/autoload.php";

$dao = new \DAO\DAO();

$dao->useTable = 'users';
$dao->name     = 'User';

$users = $dao->find('all', array(
            'conditions' => array( 'Fisica.nome LIKE' => 'bruno%' ),
            'fields'     => array('Fisica.nome', 'User.email'),
            'order'      => array(' Fisica.nome  DESC'),
            'limit'      => array(0,23), 
            'JOINS'      => array(
                                array(
                                    'join'  => 'LEFT JOIN',
                                    'table' => 'fisica',
                                    'as'    => 'Fisica',
                                    'id'    => 'id',
                                    'parent_id' => 'pessoas_id',
                                ),
                                array(
                                    'join'  => 'LEFT JOIN',
                                    'table' => 'pessoas',
                                    'as'    => 'Pessoa',
                                    'id'    => 'id',
                                    'parent_id' => 'pessoas_id',
                                )
                            ) 
        ));

echo '<pre>'; 
print_r($users);
    
} catch (\Exception $ex) {
    echo $ex->getMessage();
}