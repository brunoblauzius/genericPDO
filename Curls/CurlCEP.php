<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Curl
 *
 * @author bruno.blauzius
 */
class CurlCEP {
    
    
    private $url = 'http://www.buscacep.correios.com.br/servicos/dnec/consultaLogradouroAction.do';
    
    private $_post = null;
    
    private $contador = 0;
    
    private $parametros;
    
    private $estados = array(
        "AC"=>"Acre",
        "AL"=>"Alagoas",
        "AP"=>"Amapá",
        "AM"=>"Amazonas",
        "BA"=>"Bahia",
        "CE"=>"Ceará",
        "DF"=>"Distrito Federal",
        "ES"=>"Espírito Santo",
        "GO"=>"Goiás",
        "MA"=>"Maranhão",
        "MT"=>"Mato Grosso",
        "MS"=>"Mato Grosso do Sul",
        "MG"=>"Minas Gerais",
        "PA"=>"Pará",
        "PB"=>"Paraíba",
        "PR"=>"Paraná",
        "PE"=>"Pernambuco",
        "PI"=>"Piauí",
        "RJ"=>"Rio de Janeiro",
        "RN"=>"Rio Grande do Norte",
        "RS"=>"Rio Grande do Sul",
        "RO"=>"Rondônia",
        "RR"=>"Roraima",
        "SC"=>"Santa Catarina",
        "SP"=>"São Paulo",
        "SE"=>"Sergipe",
        "TO"=>"Tocantins",
    );
    
    
    public function __construct( $valorDeBusca ) {
        $parametros = array(
            'relaxation' => utf8_decode( $valorDeBusca ),
            'Metodo' => 'listaLogradouro',
            'TipoConsulta' => 'relaxation',
            'StartRow' => 1,
            'EndRow' => 10,
            );

        $this->parametros = $parametros;
        $this->_post = http_build_query( $parametros , '&' );
    }
    
    public function consultaCep(){
        try {
            
            $this->isNull($this->parametros['relaxation']);
            
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL , $this->url );
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION , TRUE );
            curl_setopt($ch, CURLOPT_AUTOREFERER , TRUE );
            curl_setopt($ch, CURLOPT_POST , TRUE );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER , TRUE );
            curl_setopt($ch, CURLOPT_POSTFIELDS ,  $this->_post );

            $page = curl_exec($ch);

            $div = preg_split('/<(div)>/i', $page);
            $tr = preg_split('/<(tr)>/i', $div[1]);
            $retorno = preg_split('/<(td (.*?))>/i', $tr[0]);
            unset($retorno[0]);
            curl_close($ch);


            return json_encode( $this->geralista($retorno) );
            
        } catch (Exception $ex) {
            return json_encode(array(
                'erro' => TRUE,
                'mensagem' => $ex->getMessage(),
                'resultado' => NULL
            ));
        }
    }
    
    
    private function geralista( $lista ){
        try{
            $contador = 1;
            $contadorArray = 0;
            $novoArray = array();
            for ($index = 1; $index <= count($lista) ; $index++) {
                $valor = str_replace('-', '', trim(strip_tags($lista[$index])));
                if( !is_numeric($valor) ){
                    $novoArray[$contadorArray][$contador] = $valor;
                    $contador ++;
                } else {
                   $novoArray[$contadorArray][$contador] = $valor;
                   $contador = 1;
                   $contadorArray ++;
                }
            }
            return $this->retornaCeps($novoArray);
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    
    private function retornaCeps( $oldArray = NULL ){
        try{
           if( !is_null($oldArray) ){
                $novoArray = array();
                foreach ($oldArray as $value) {
                    $novoArray[] = array(
                        'logradouro' => utf8_encode($value[1]),
                        'bairro'     => utf8_encode($value[2]),
                        'cidade'     => utf8_encode($value[3]),
                        'estado'     => $this->estados[strtoupper($value[4])],
                        'uf'         => utf8_encode($value[4]),
                        'cep'        => utf8_encode($value[5]),
                    );
                }

                if( !empty($novoArray) ){
                    $novoArray = array(
                        'erro' => false,
                        'mensagem' => 'Sucesso',
                        'resultado' => $novoArray
                    );
                } else {
                    throw new Exception('Nenhum registro foi encontrado...');
                }
                
                return $novoArray;
            } 
        } catch (Exception $ex) {
            throw $ex;
        }
    }  
    
    public function isNull( $valor ){
        try{
            if(empty($valor)){
                throw new Exception('Cep está vazio favor informar!');
            }
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
}


$cep = new CurlCEP($_GET['valor']);
echo $cep->consultaCep();