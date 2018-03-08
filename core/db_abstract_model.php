<?php
abstract class DBAbstractModel {

    private static  $db_host    = 'localhost';
    private static  $db_user    = 'miendodoncia';
    private static  $db_pass    = 'z9S2}yh}2ZsT';
    protected       $db_name    = 'miendodoncia_miendodoncia';
    public          $db_result  = '';
    protected       $query      = '';
    public          $rows       = array();
    public          $row        = array();
    protected       $rowAffected= 0;
    private         $conn       = null;
    public          $msj        = '';
    public          $err        = '';
    public          $module     = 0;
    public          $_secuencia = 0;
    
    # los siguientes métodos pueden definirse con exactitud y no son abstractos
	# Conectar a la base de datos
	private function open_connection() {
	    $this->conn = new mysqli(self::$db_host, self::$db_user, 
	                             self::$db_pass, $this->db_name);
            $this->conn->set_charset("utf8");
	}

	# Desconectar la base de datos
	private function close_connection() {
		$this->conn->close();
	}

	# Ejecutar un query simple del tipo INSERT, DELETE, UPDATE
	protected function execute_single_query() {
        $this->open_connection();
        $this->conn->query($this->query);
        $this->db_result = mysqli_affected_rows($this->conn);
        $this->close_connection();

        return $this->db_result;
	}

	# Traer resultados de una consulta en un Array
	protected function get_results_from_query() {
        $this->open_connection();
        $result = $this->conn->query($this->query);
        while ($this->rows[] = $result->fetch_assoc());
        //$result->close();
        //self::close_connection();
        array_pop($this->rows);
	}
	
    protected function get_results_from_sp() {
        $this->open_connection();
        array_pop($this->rows);
        $this->conn->query($this->query);
        $result = $this->conn->query( "SELECT @cDesError,@cNumError" );
        $this->row = $result->fetch_assoc();

	}
        
	# Traer 1 resultado de una consulta
	protected function get_result_from_query() {
            $this->open_connection();
            $result = $this->conn->query($this->query);
            $this->row = $result->fetch_assoc();
            //$result->close();
            $this->close_connection();
	}
}
?>
