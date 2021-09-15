<?php
////////////////////////////////////////////////////////////////////////////////
/////////////////////////        MASIVO MIPRES         /////////////////////////
/////////////////////////      PIJAOS SALUD EPSI      //////////////////////////
/////////////////////////  CONEXION A LA BASE DE DATOS   ///////////////////////
/////////////////////////  DEPARTAMENTO DE DESARROLLO  /////////////////////////
///////       CONEXION A LA BASE DE DATOS Y METODOS GENERALES SQL     //////////
////////////////////////////////////////////////////////////////////////////////


require 'global.php';

$conn = array("Database" => DB_NAME, "UID" => DB_USERNAME, "PWD" => DB_PASSWORD, "CharacterSet" => DB_ENCODE);

$conexion = sqlsrv_connect(DB_HOST, $conn);

if ($conexion) {

    //echo 'Conexión establecida.';
} else {

    echo 'Conexión no se pudo establecer. <br>';
    die(print_r(sqlsrv_errors(), true));
}


//si no existe la funcion de consulta, la definimos
if (!function_exists('ejecutarConsulta')) {
    
   function ejecutarConsulta($sql) {
       
        global $conexion;
        $query = sqlsrv_query($conexion, $sql);
        return $query;
    }

    //retorna en una fila el resultado de una consulta
    function ejecutarConsultaSimpleFila($sql) {

        global $conexion;
        $query = sqlsrv_query($conexion, $sql);
        $row = sqlsrv_fetch_object($query);
        return $row;
    }


    //limpia caracteres especiales antes de consultar
    function limpiarCadena($str) {

        $str = trim($str);
        $str = stripcslashes($str);
        $str = htmlspecialchars($str);

        return $str;
    }

}
 