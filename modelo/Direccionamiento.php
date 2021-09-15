<?php

////////////////////////////////////////////////////////////////////////////////
/////////////////////////        MASIVO MIPRES         /////////////////////////
/////////////////////////      PIJAOS SALUD EPSI      //////////////////////////
///////////////////         MODELO DIRECCIONAMIENTOS          //////////////////
/////////////////////////  DEPARTAMENTO DE DESARROLLO  /////////////////////////
////////////////////////////////////////////////////////////////////////////////

//Incluimos inicialmete la conexion a la base de datos
require_once '../config/conexion.php';

class Direccionamiento {

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
    public function url_direccionamiento($regimen, $codurl) {

        $sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED "
                . "SELECT T.DES_TEM_TOKEN, U.DES_URL FROM PRS_TEM_TOKEN T, PRS_URL_SERVICES U WITH (NOLOCK) WHERE T.TIP_TEM_TOKEN = '$regimen' AND U.COD_URL = '$codurl'";

        return ejecutarConsulta($sql);
    }

   /**
    * Metodo que inserta o actiualiza los direccionamientos S y C
    * @param String $ID
    * @param String $IDDireccionamiento
    * @param String $NoPrescripcion
    * @param String $TipoTec
    * @param String $ConTec
    * @param String $TipoIDPaciente
    * @param String $NoIDPaciente
    * @param String $NoEntrega
    * @param String $NoSubEntrega
    * @param String $TipoIDProv
    * @param String $NoIDProv
    * @param String $CodMunEnt
    * @param date $FecMaxEnt
    * @param String $CantTotAEntregar
    * @param String $DirPaciente
    * @param String $CodSerTecAEntregar
    * @param String $NoIDEPS
    * @param String $CodEPS
    * @param date $FecDireccionamiento
    * @param date $EstDireccionamiento
    * @param String $FecAnulacion
    * @return obj
    */
    public function insertar_direccionamiento($ID, $IDDireccionamiento, $NoPrescripcion, $TipoTec, $ConTec, $TipoIDPaciente,
            $NoIDPaciente, $NoEntrega, $NoSubEntrega, $TipoIDProv, $NoIDProv, $CodMunEnt, $FecMaxEnt, $CantTotAEntregar,
            $DirPaciente, $CodSerTecAEntregar, $NoIDEPS, $CodEPS, $FecDireccionamiento, $EstDireccionamiento, $FecAnulacion) {


        $sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED 
                IF NOT EXISTS (SELECT * FROM MIPRES_DIRECCIONAMIENTOS WHERE ID = '$ID' AND IDDIRECCIONAMIENTO = '$IDDireccionamiento') 
                BEGIN 
                INSERT INTO [MIPRES_DIRECCIONAMIENTOS](
                [ID]
                ,[IDDireccionamiento]
                ,[NoPrescripcion]
                ,[TipoTec]
                ,[ConTec]
                ,[TipoIDPaciente]
                ,[NoIDPaciente]
                ,[NoEntrega]
                ,[NoSubEntrega]
                ,[TipoIDProv]
                ,[NoIDProv]
                ,[CodMunEnt]
                ,[FecMaxEnt]
                ,[CantTotAEntregar]
                ,[DirPaciente]
                ,[CodSerTecAEntregar]
                ,[NoIDEPS]
                ,[CodEPS]
                ,[FecDireccionamiento] 
                ,[EstDireccionamiento]
                ,[FecAnulacion]) 
                VALUES (
                '$ID'
                , '$IDDireccionamiento'
                , '$NoPrescripcion'
                , '$TipoTec'
                , '$ConTec'
                , '$TipoIDPaciente'
                , '$NoIDPaciente'
                , '$NoEntrega'
                , '$NoSubEntrega'
                , '$TipoIDProv'
                , '$NoIDProv'
                , '$CodMunEnt'
                , '$FecMaxEnt'
                , '$CantTotAEntregar'
                , '$DirPaciente'
                , '$CodSerTecAEntregar'
                , '$NoIDEPS'
                , '$CodEPS'
                , '$FecDireccionamiento'
                , '$EstDireccionamiento'
                , '$FecAnulacion') 
                END 
                ELSE BEGIN 
                UPDATE [MIPRES_DIRECCIONAMIENTOS] WITH (ROWLOCK) SET 
                [ID] = '$ID'
                , [IDDireccionamiento] = '$IDDireccionamiento'
                , [NoPrescripcion] = '$NoPrescripcion'
                , [TipoTec] = '$TipoTec'
                , [ConTec] = '$ConTec'
                , [TipoIDPaciente] = '$TipoIDPaciente'
                , [NoIDPaciente] = '$NoIDPaciente'
                , [NoEntrega] = '$NoEntrega', [NoSubEntrega] = '$NoSubEntrega'
                , [TipoIDProv] = '$TipoIDProv'
                , [NoIDProv] = '$NoIDProv'
                , [CodMunEnt] = '$CodMunEnt'
                , [FecMaxEnt] = '$FecMaxEnt'
                , [CantTotAEntregar] = '$CantTotAEntregar'
                , [DirPaciente] = '$DirPaciente'
                , [CodSerTecAEntregar] = '$CodSerTecAEntregar'
                , [NoIDEPS] = '$NoIDEPS'
                , [CodEPS] = '$CodEPS'
                , [FecDireccionamiento] = '$FecDireccionamiento'
                , [EstDireccionamiento] = '$EstDireccionamiento', 
                [FecAnulacion] = '$FecAnulacion' WHERE ID = '$ID' AND IDDIRECCIONAMIENTO = '$IDDireccionamiento' 
                END";

        return ejecutarConsulta($sql);
    }

}
