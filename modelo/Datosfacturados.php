<?php

////////////////////////////////////////////////////////////////////////////////
/////////////////////////        MASIVO MIPRES         /////////////////////////
/////////////////////////      PIJAOS SALUD EPSI      //////////////////////////
///////////////////         MODELO DATOS FACTURADOS        /////////////////////
/////////////////////////  DEPARTAMENTO DE DESARROLLO  /////////////////////////
////////////////////////////////////////////////////////////////////////////////
//Incluimos inicialmete la conexion a la base de datos
require_once '../config/conexion.php';

class Datosfacturados {

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
    public function url_datosfacturados($regimen, $codurl) {

        $sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED "
                . "SELECT T.DES_TEM_TOKEN, U.DES_URL FROM PRS_TEM_TOKEN T, PRS_URL_SERVICES U WITH (NOLOCK) WHERE T.TIP_TEM_TOKEN = '$regimen' AND U.COD_URL = '$codurl'";

        return ejecutarConsulta($sql);
    }

    /**
     * Metodo que inserta los datos facturados S y C
     * @param String $ID
     * @param String $IDDatosFacturado
     * @param String $NoPrescripcion
     * @param String $TipoTec
     * @param String $ConTec
     * @param String $TipoIDPaciente
     * @param String $NoIDPaciente
     * @param String $NoEntrega
     * @param String $CompAdm
     * @param String $CodCompAdm
     * @param String $CodHom
     * @param String $UniCompAdm
     * @param String $UniDispHom
     * @param String $ValUnMiCon
     * @param String $CantTotEnt
     * @param String $ValTotCompAdm
     * @param String $ValTotHom
     * @param date $FecDatosFacturado
     * @param String $EstDatosFacturado
     * @param date $FecAnulacion
     * @return obj
     */
    public function insertar_datoFacturado($ID, $IDDatosFacturado, $NoPrescripcion, $TipoTec, $ConTec, $TipoIDPaciente, $NoIDPaciente,
            $NoEntrega, $CompAdm, $CodCompAdm, $CodHom, $UniCompAdm, $UniDispHom, $ValUnMiCon, $CantTotEnt, $ValTotCompAdm, $ValTotHom,
            $FecDatosFacturado, $EstDatosFacturado, $FecAnulacion) {


        $sql = "IF NOT EXISTS (SELECT * FROM MIPRES_DATOS_FACTURADOS WHERE ID = '$ID' AND IDDatosFacturado = '$IDDatosFacturado' ) BEGIN
                INSERT INTO [dbo].[MIPRES_DATOS_FACTURADOS]
                           ([ID]
                           ,[IDDatosFacturado]
                           ,[NoPrescripcion]
                           ,[TipoTec]
                           ,[ConTec]
                           ,[TipoIDPaciente]
                           ,[NoIDPaciente]
                           ,[NoEntrega]
                           ,[CompAdm]
                           ,[CodCompAdm]
                           ,[CodHom]
                           ,[UniCompAdm]
                           ,[UniDispHom]
                           ,[ValUnMiCon]
                           ,[CantTotEnt]
                           ,[ValTotCompAdm]
                           ,[ValTotHom]
                           ,[FecDatosFacturado]
                           ,[EstDatosFacturado]
                           ,[FecAnulacion])
                     VALUES
                           ('$ID'
                           ,'$IDDatosFacturado'
                           ,'$NoPrescripcion'
                           ,'$TipoTec'
                           ,'$ConTec'
                           ,'$TipoIDPaciente'
                           ,'$NoIDPaciente'
                           ,'$NoEntrega'
                           ,'$CompAdm'
                           ,'$CodCompAdm'
                           ,'$CodHom'
                           ,'$UniCompAdm'
                           ,'$UniDispHom'
                           ,'$ValUnMiCon'
                           ,'$CantTotEnt'
                           ,'$ValTotCompAdm'
                           ,'$ValTotHom'
                           ,'$FecDatosFacturado'
                           ,'$EstDatosFacturado'
                           ,'$FecAnulacion'
                           )
                END ELSE BEGIN 
                UPDATE [dbo].[MIPRES_DATOS_FACTURADOS]
                   SET 
                       [NoPrescripcion] = '$NoPrescripcion'
                      ,[TipoTec] = '$TipoTec'
                      ,[ConTec] = '$ConTec'
                      ,[TipoIDPaciente] = '$TipoIDPaciente'
                      ,[NoIDPaciente] = '$NoIDPaciente'
                      ,[NoEntrega] = '$NoEntrega'
                      ,[CompAdm] = '$CompAdm'
                      ,[CodCompAdm] = '$CodCompAdm'
                      ,[CodHom] = '$CodHom'
                      ,[UniCompAdm] = '$UniCompAdm'
                      ,[UniDispHom] = '$UniDispHom'
                      ,[ValUnMiCon] = '$ValUnMiCon'
                      ,[CantTotEnt] = '$CantTotEnt'
                      ,[ValTotCompAdm] = '$ValTotCompAdm'
                      ,[ValTotHom] = '$ValTotHom'
                      ,[FecDatosFacturado] = '$FecDatosFacturado'
                      ,[EstDatosFacturado] = '$EstDatosFacturado'
                      ,[FecAnulacion] = '$FecAnulacion'
                 WHERE ID = '$ID' AND IDDatosFacturado = '$IDDatosFacturado'
                END";


        return ejecutarConsulta($sql);
    }

}
