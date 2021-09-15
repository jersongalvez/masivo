<?php

////////////////////////////////////////////////////////////////////////////////
/////////////////////////        MASIVO MIPRES         /////////////////////////
/////////////////////////      PIJAOS SALUD EPSI      //////////////////////////
///////////////////         MODELO NOVEDAD PRESCRIPCION       //////////////////
/////////////////////////  DEPARTAMENTO DE DESARROLLO  /////////////////////////
////////////////////////////////////////////////////////////////////////////////
//Incluimos inicialmete la conexion a la base de datos
require_once '../config/conexion.php';

class Novedadprescripcion {

    //Implementamos nuestro constructor
    public function __construct() {
        //se deja vacio para implementar instancias hacia esta clase
        //sin enviar parametro
    }

    /**
     * Metodo que obtiene el token permanente del Web Service
     * @param String $regimen
     * @param String $codurl
     * @return obj
     */
    public function url_novPrescripcion($regimen, $codurl) {

        if ($regimen === 'S') {

            $sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED "
                    . "SELECT C.PRETOCKENSUB AS TOKENP, U.DES_URL FROM COMPANIA C, PRS_URL_SERVICES U WITH (NOLOCK) WHERE U.COD_URL = '$codurl'";
        } else {

            $sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED "
                    . "SELECT C.PRETOCKEN AS TOKENP, U.DES_URL FROM COMPANIA C, PRS_URL_SERVICES U WITH (NOLOCK) WHERE U.COD_URL = '$codurl'";
        }

        return ejecutarConsulta($sql);
    }

    /**
     * Metodo que inserta las novedades de las prescripciones S y C
     * @param String $NoPrescripcion
     * @param String $NoPrescripcionF
     * @param int $TipoNov
     * @param date $FNov
     * @return obj
     */
    public function insertar_novPrescripcion($NoPrescripcion, $NoPrescripcionF, $TipoNov, $FNov) {

        $sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED
                IF NOT EXISTS (SELECT NoPrescripcion FROM MIPRES_NOVEDADES WHERE NoPrescripcion = '$NoPrescripcion' AND NoPrescripcionF = '$NoPrescripcionF' )
                BEGIN 

                INSERT INTO [dbo].[MIPRES_NOVEDADES]
                ([TipoNov]
                ,[NoPrescripcion]
                ,[NoPrescripcionF]
                ,[FNov])
                VALUES
                ('$TipoNov'
                ,'$NoPrescripcion'
                ,'$NoPrescripcionF'
                ,'$FNov')
                    
                END ELSE BEGIN 
                UPDATE [dbo].[MIPRES_NOVEDADES] WITH (ROWLOCK) SET 
                [TipoNov] = '$TipoNov'
                ,[NoPrescripcion] = '$NoPrescripcion'
                ,[NoPrescripcionF] = '$NoPrescripcionF'
                ,[FNov] = '$FNov'
                WHERE NoPrescripcion = 'NoPrescripcion' AND NoPrescripcionF = 'NoPrescripcionF'
                END";

        return ejecutarConsulta($sql);
    }

    /**
     * Metodo que actualiza una prescripcion con estado 1: Modificación o 2: Anulación
     * @param type $TipoNov
     * @param type $NoPrescripcion
     * @return obj
     */
    public function actualizar_prescripcion($TipoNov, $NoPrescripcion) {

        $sql = "UPDATE [dbo].[MIPRES_PRESCRIPCION] WITH (ROWLOCK) SET [ESTPRES] = '$TipoNov' WHERE [NOPRESCRIPCION] = '$NoPrescripcion'";

        return ejecutarConsulta($sql);
    }

}
