<?php

////////////////////////////////////////////////////////////////////////////////
/////////////////////////        MASIVO MIPRES         /////////////////////////
/////////////////////////      PIJAOS SALUD EPSI      //////////////////////////
///////////////////          MODELO VALIDACION TOKEN          //////////////////
/////////////////////////  DEPARTAMENTO DE DESARROLLO  /////////////////////////
////////////////////////////////////////////////////////////////////////////////

//Incluimos inicialmete la conexion a la base de datos
require_once '../config/conexion.php';

class Token {

    //Implementamos nuestro constructor
    public function __construct() {
        //se deja vacio para implementar instancias hacia esta clase
        //sin enviar parametro
    }

    /**
     * Metodo que obtiene el token actual del Web Service
     * @param String $regimen
     * @param String $codurl
     * @return obj
     */
    public function validar_token($regimen, $codurl) {

        $sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED "
                . "SELECT T.DES_TEM_TOKEN, U.DES_URL FROM PRS_TEM_TOKEN T, PRS_URL_SERVICES U WITH (NOLOCK) WHERE T.TIP_TEM_TOKEN = '$regimen' AND U.COD_URL = '$codurl'";

        return ejecutarConsulta($sql);
    }

    /**
     * Metodo que obtiene el token permante otorgado por MIPRES de acuerdo
     * con el regimen, se utiliza para generar el token temporal requerido por
     * las transacciones echas al Web Service
     * @param String $regimen
     */
    public function obtener_tokenPermanente($regimen) {

        if ($regimen === 'S') {

            $sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED "
                    . "SELECT U.DES_URL, C.NUM_DOCUMENTO, C.PRETOCKENSUB AS TOKENP FROM PRS_URL_SERVICES U, COMPANIA C WHERE COD_URL = '1' ";
        } else {

            $sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED "
                    . "SELECT U.DES_URL, C.NUM_DOCUMENTO, C.PRETOCKEN AS TOKENP FROM PRS_URL_SERVICES U, COMPANIA C WHERE COD_URL = '1' ";
        }

        return ejecutarConsulta($sql);
    }

    /**
     * Metodo que almacena el token generado por el Web Service
     * @param String $regimen
     * @param String $token
     */
    public function guardar_token($regimen, $token) {

        $sql = "UPDATE PRS_TEM_TOKEN WITH (ROWLOCK) SET DES_TEM_TOKEN = '$token', USU_TEM_TOKEN = 'MASIVO', FEC_TEM_TOKEN = CURRENT_TIMESTAMP "
                . "WHERE TIP_TEM_TOKEN = '$regimen' ";

        return ejecutarConsulta($sql);
    }

}
