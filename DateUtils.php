<?php



class DateUtils {
    
    
    private $dataInicio, $dataFim;

    private $dataDiff;
    
    const DATA_INVALIDA     = 'Data inserida incorreta!';
    
    const DATA_FORMAT       = '%Y-%m-%d %H:%i:%s';
    
    const HOUR_FORMAT       = '%H:%i:%s';
    
    const  DATA_FORMAT_VIEW = '%d/%m/%Y %H:%i:%s';


    public function DateUtils(){}
    
    public function __construct( $dataInicio, $dataFim ) {
        try{
            date_default_timezone_set(date("e"));             
            $this->dataInicio = new DateTime($this->validarData($dataInicio), new DateTimeZone( date('e') ));
            $this->dataFim    = new DateTime($this->validarData($dataFim), new DateTimeZone( date('e') ));
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }
    
    
    public function calculaDiffData(){
        $this->dataDiff = $this->dataFim->diff( $this->dataInicio );
        //print_r( $this->dataDiff );
    }
    
    /**
     * @todo metodo que retorna a diferença em dias;
     * @return int;
     */
    public function diffDays(){
        return $this->dataDiff->days;
    }
    
    /**
     * @todo retorna as horas restantes
     * @return string
     */
    public function diffHours(){
        return $this->dataDiff->format( self::HOUR_FORMAT );
    }

    /**
     * @todo retorno a data convertida visualmente padrão brasil
     * @return string
     */
    public function dataConvertidaDataBase(){
        return $this->dataDiff->format( self::DATA_FORMAT );
    }
    
    /**
     * @todo retorno a data convertida visualmente padrão brasil
     * @return string
     */
    public function dataConvertidaView(){
        return $this->dataDiff->format( self::DATA_FORMAT_VIEW );
    }
    
    
    
    /**
     * @todo metodo que converte a data para YYYY-MM-DD HH:II:SS
     * @param string $data
     * @return string
     * @throws Exception string
     */
    private function validarData( $data ){
        try{
            if(preg_match('/[0-9]{4}\-[0-9]{2}\-[0-9]{2}/', $data )){
                // faço um explode na data para pegar data, mes e ano
                $dataEx = explode('-', $data);
            } else {
                
                $dataEx = explode('/', $data);
                $dataEx = array_reverse($dataEx);
                
                //verifico se exste hora na data
                if( strpos( $dataEx[0], ':' ) !== FALSE ) {
                    $horaEx = explode(' ', $dataEx[0]);
                    $dataEx[0] = $horaEx[0];
                    $data = join('-', $dataEx) . ' '. $horaEx[1];
                } else {
                    $data = join('-', $dataEx);
                }
            } 
            //faço a verificação se minha data está no formato correto
            if (!checkdate($dataEx[1], $dataEx[2], $dataEx[0])) {
                throw new Exception( self::DATA_INVALIDA );
            }
            
            return $data;
            
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * @todo metodo destrutor, usado para destruir os atributos apos usados
     */
    public function __destruct () {
        unset($this->dataDiff);
        unset($this->dataInicio);
        unset($this->dataFim);
    }
    
}

//$dataUtils = new DateUtils( date('Y-m-d H:i:s'), '2014-12-16 00:00:00' );
$dataUtils->calculaDiffData();
echo $dataUtils->dataConvertidaDataBase() .'<br>';
echo $dataUtils->dataConvertidaView() .'<br>';
echo $dataUtils->diffDays() .' Dias <br>';
echo $dataUtils->diffHours() .'<br>';