<?php

////////////////////////////////////////////////////////////////////////////////
/////////////////////////        MASIVO MIPRES         /////////////////////////
/////////////////////////      PIJAOS SALUD EPSI      //////////////////////////
///////////////////          MODELO SUMINISTRO VER 1          //////////////////
/////////////////////////  DEPARTAMENTO DE DESARROLLO  /////////////////////////
////////////////////////////////////////////////////////////////////////////////
//Incluimos inicialmete la conexion a la base de datos
require_once '../config/conexion.php';

class Suministrov1 {

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
    public function url_suministroV1($regimen, $codurl) {

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
     * Metodo que inserta los suministros version uno (1) S y C
     * @param String $ID
     * @param String $NoPrescripcion
     * @param String $TipoTecnologia
     * @param String $ConOrden
     * @param String $TipoIDEntidad
     * @param String $NoIdPrestSumServ
     * @param String $CodHabIPS
     * @param String $FSum
     * @param String $TipoIDPaciente
     * @param String $NroIDPaciente
     * @param String $EntregaMes
     * @param String $UltEntrega
     * @param String $NoEntParcial
     * @param String $EntregaCompleta
     * @param String $NoPrescripcionAsociada
     * @param String $ConOrdenAsociada
     * @param String $CausaNoEntrega
     * @param String $CodTecnEntregado
     * @param String $CantidadTotalEntregada
     * @param String $ValorEntregado
     * @param date $FReporte
     * @param String $NoLote
     * @param date $EstSuministro
     * @param date $FecAnulacion
     * @return obj
     */
    public function insertar_suministroV1($ID, $NoPrescripcion, $TipoTecnologia, $ConOrden, $TipoIDEntidad, $NoIdPrestSumServ, $CodHabIPS,
            $FSum, $TipoIDPaciente, $NroIDPaciente, $EntregaMes, $UltEntrega, $NoEntParcial, $EntregaCompleta, $NoPrescripcionAsociada,
            $ConOrdenAsociada, $CausaNoEntrega, $CodTecnEntregado, $CantidadTotalEntregada, $ValorEntregado, $FReporte, $NoLote,
            $EstSuministro, $FecAnulacion) {


        $sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED
                IF NOT EXISTS (SELECT * FROM MIPRES_SUMINISTRO_V1 WHERE ID = '$ID' AND NoPrescripcion = '$NoPrescripcion' ) BEGIN
                    INSERT INTO [dbo].[MIPRES_SUMINISTRO_V1]
                   ([ID]
                   ,[NoPrescripcion]
                   ,[TipoTecnologia]
                   ,[ConOrden]
                   ,[TipoIDEntidad]
                   ,[NoIdPrestSumServ]
                   ,[CodHabIPS]
                   ,[FSum]
                   ,[TipoIDPaciente]
                   ,[NroIDPaciente]
                   ,[EntregaMes]
                   ,[UltEntrega]
                   ,[NoEntParcial]
                   ,[EntregaCompleta]
                   ,[NoPrescripcionAsociada]
                   ,[ConOrdenAsociada]
                   ,[CausaNoEntrega]
                   ,[CodTecnEntregado]
                   ,[CantidadTotalEntregada]
                   ,[ValorEntregado]
                   ,[FReporte]
                   ,[NoLote]
                   ,[EstSuministro]
                   ,[FecAnulacion])
                VALUES
                   ('$ID'
                   ,'$NoPrescripcion'
                   ,'$TipoTecnologia'
                   ,'$ConOrden'
                   ,'$TipoIDEntidad'
                   ,'$NoIdPrestSumServ'
                   ,'$CodHabIPS'
                   ,'$FSum'
                   ,'$TipoIDPaciente'
                   ,'$NroIDPaciente'
                   ,'$EntregaMes'
                   ,'$UltEntrega'
                   ,'$NoEntParcial'
                   ,'$EntregaCompleta'
                   ,'$NoPrescripcionAsociada'
                   ,'$ConOrdenAsociada'
                   ,'$CausaNoEntrega'
                   ,'$CodTecnEntregado'
                   ,'$CantidadTotalEntregada'
                   ,'$ValorEntregado'
                   ,'$FReporte'
                   ,'$NoLote'
                   ,'$EstSuministro'
                   ,'$FecAnulacion'
                   )

                END ELSE BEGIN 
                UPDATE [dbo].[MIPRES_SUMINISTRO_V1] WITH (ROWLOCK)
                   SET 
                       [TipoTecnologia] = '$TipoTecnologia'
                      ,[ConOrden] = '$ConOrden'
                      ,[TipoIDEntidad] = '$TipoIDEntidad'
                      ,[NoIdPrestSumServ] = '$NoIdPrestSumServ'
                      ,[CodHabIPS] = '$CodHabIPS'
                      ,[FSum] = '$FSum'
                      ,[TipoIDPaciente] = '$TipoIDPaciente'
                      ,[NroIDPaciente] = '$NroIDPaciente'
                      ,[EntregaMes] = '$EntregaMes'
                      ,[UltEntrega] = '$UltEntrega'
                      ,[NoEntParcial] = '$NoEntParcial'
                      ,[EntregaCompleta] = '$EntregaCompleta'
                      ,[NoPrescripcionAsociada] = '$NoPrescripcionAsociada'
                      ,[ConOrdenAsociada] = '$ConOrdenAsociada'
                      ,[CausaNoEntrega] = '$CausaNoEntrega'
                      ,[CodTecnEntregado] = '$CodTecnEntregado'
                      ,[CantidadTotalEntregada] = '$CantidadTotalEntregada'
                      ,[ValorEntregado] = '$ValorEntregado'
                      ,[FReporte] = '$FReporte'
                      ,[NoLote] = '$NoLote'
                      ,[EstSuministro] = '$EstSuministro'
                      ,[FecAnulacion] =  '$FecAnulacion'
                 WHERE ID = '$ID' AND NoPrescripcion = '$NoPrescripcion'
                END";


        return ejecutarConsulta($sql);
    }

}
