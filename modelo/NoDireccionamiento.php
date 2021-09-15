<?php

////////////////////////////////////////////////////////////////////////////////
/////////////////////////        MASIVO MIPRES         /////////////////////////
/////////////////////////      PIJAOS SALUD EPSI      //////////////////////////
///////////////////         MODELO NO DIRECCIONAMIENTOS       //////////////////
/////////////////////////  DEPARTAMENTO DE DESARROLLO  /////////////////////////
////////////////////////////////////////////////////////////////////////////////
//Incluimos inicialmete la conexion a la base de datos
require_once '../config/conexion.php';

class NoDireccionamiento {

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
    public function url_noDireccionamiento($regimen, $codurl) {

        $sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED "
                . "SELECT T.DES_TEM_TOKEN, U.DES_URL FROM PRS_TEM_TOKEN T, PRS_URL_SERVICES U WITH (NOLOCK) WHERE T.TIP_TEM_TOKEN = '$regimen' AND U.COD_URL = '$codurl'";

        return ejecutarConsulta($sql);
    }

    /**
     * Metodo que inserta o actiualiza los No direccionamientos S y C
     * @param String $ID
     * @param String $IDNODireccionamiento
     * @param String $NoPrescripcion
     * @param String $TipoTec
     * @param String $ConTec
     * @param String $TipoIDPaciente
     * @param String $NoIDPaciente
     * @param String $NoPrescripcionAsociada
     * @param String $ConTecAsociada
     * @param String $CausaNoEntrega
     * @param date $FecNODireccionamiento
     * @param String $EstNODireccionamiento
     * @param date $FecAnulacion
     * @return type
     */
    public function insertar_noDireccionamiento($ID, $IDNODireccionamiento, $NoPrescripcion, $TipoTec, $ConTec, $TipoIDPaciente,
            $NoIDPaciente, $NoPrescripcionAsociada, $ConTecAsociada, $CausaNoEntrega, $FecNODireccionamiento,
            $EstNODireccionamiento, $FecAnulacion) {

        $sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED
                IF NOT EXISTS (SELECT * FROM MIPRES_NO_DIRECCIONAMIENTOS WHERE ID = '$ID' AND IDNODIRECCIONAMIENTO = '$IDNODireccionamiento') BEGIN
                INSERT INTO [dbo].[MIPRES_NO_DIRECCIONAMIENTOS]
                           ([ID]
                           ,[IDNODireccionamiento]
                           ,[NoPrescripcion]
                           ,[TipoTec]
                           ,[ConTec]
                           ,[TipoIDPaciente]
                           ,[NoIDPaciente]
                           ,[NoPrescripcionAsociada]
                           ,[ConTecAsociada]
                           ,[CausaNoEntrega]
                           ,[FecNODireccionamiento]
                           ,[EstNODireccionamiento]
                           ,[FecAnulacion]
                           ,[USUARIO_NO_DIRECCIONAMIENTO]
                           ,[FECHA_NO_DIRECCIONAMIENTO])
                     VALUES
                           ('$ID'
                           ,'$IDNODireccionamiento'
                           ,'$NoPrescripcion'
                           ,'$TipoTec'
                           ,'$ConTec'
                           ,'$TipoIDPaciente'
                           ,'$NoIDPaciente'
                           ,'$NoPrescripcionAsociada'
                           ,'$ConTecAsociada'
                           ,'$CausaNoEntrega'
                           ,'$FecNODireccionamiento'
                           ,'$EstNODireccionamiento'
                           ,'$FecAnulacion'
                           ,'N/A'
                           ,CURRENT_TIMESTAMP)
                END ELSE BEGIN 
                UPDATE [dbo].[MIPRES_NO_DIRECCIONAMIENTOS] WITH (ROWLOCK)
                   SET 
                       [NoPrescripcion] = '$NoPrescripcion'
                      ,[TipoTec] = '$TipoTec'
                      ,[ConTec] = '$ConTec'
                      ,[TipoIDPaciente] = '$TipoIDPaciente'
                      ,[NoIDPaciente] = '$NoIDPaciente'
                      ,[NoPrescripcionAsociada] = '$NoPrescripcionAsociada'
                      ,[ConTecAsociada] = '$ConTecAsociada'
                      ,[CausaNoEntrega] = '$CausaNoEntrega'
                      ,[FecNODireccionamiento] = '$FecNODireccionamiento'
                      ,[EstNODireccionamiento] = '$EstNODireccionamiento'
                      ,[FecAnulacion] = '$FecAnulacion'
                 WHERE ID = '$ID' AND IDNODIRECCIONAMIENTO = '$IDNODireccionamiento'
                END";

        return ejecutarConsulta($sql);
    }

}
