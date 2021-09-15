<?php

////////////////////////////////////////////////////////////////////////////////
/////////////////////////        MASIVO MIPRES         /////////////////////////
/////////////////////////      PIJAOS SALUD EPSI      //////////////////////////
///////////////////             MODELO SUMINISTRO             //////////////////
/////////////////////////  DEPARTAMENTO DE DESARROLLO  /////////////////////////
////////////////////////////////////////////////////////////////////////////////

//Incluimos inicialmete la conexion a la base de datos
require_once '../config/conexion.php';

class Suministro {

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
    public function url_suministro($regimen, $codurl) {

        $sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED "
                . "SELECT T.DES_TEM_TOKEN, U.DES_URL FROM PRS_TEM_TOKEN T, PRS_URL_SERVICES U WITH (NOLOCK) WHERE T.TIP_TEM_TOKEN = '$regimen' AND U.COD_URL = '$codurl'";

        return ejecutarConsulta($sql);
    }

    /**
     * Metodo que inserta o actualiza los suministros
     * @param String $ID
     * @param String $IDSuministro
     * @param String $NoPrescripcion
     * @param String $TipoTec
     * @param String $ConTec
     * @param String $TipoIDPaciente
     * @param String $NoIDPaciente
     * @param String $NoEntrega
     * @param String $UltEntrega
     * @param String $EntregaCompleta
     * @param String $CausaNoEntrega
     * @param String $NoPrescripcionAsociada
     * @param String $ConTecAsociada
     * @param String $CantTotEntregada
     * @param String $NoLote
     * @param String $ValorEntregado
     * @param date $FecSuministro
     * @param String $EstSuministro
     * @param date $FecAnulacion
     * @return obj
     */
    public function insertar_suministro($ID, $IDSuministro, $NoPrescripcion, $TipoTec, $ConTec, $TipoIDPaciente, $NoIDPaciente,
            $NoEntrega, $UltEntrega, $EntregaCompleta, $CausaNoEntrega, $NoPrescripcionAsociada, $ConTecAsociada, $CantTotEntregada,
            $NoLote, $ValorEntregado, $FecSuministro, $EstSuministro, $FecAnulacion) {

        $sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED 
                IF NOT EXISTS (SELECT * FROM MIPRES_SUMINISTRO WHERE ID = '$ID' AND IDSuministro = '$IDSuministro') 
                BEGIN 
                INSERT INTO [dbo].[MIPRES_SUMINISTRO]
                           ([ID]
                           ,[IDSuministro]
                           ,[NoPrescripcion]
                           ,[TipoTec]
                           ,[ConTec]
                           ,[TipoIDPaciente]
                           ,[NoIDPaciente]
                           ,[NoEntrega]
                           ,[UltEntrega]
                           ,[EntregaCompleta]
                           ,[CausaNoEntrega]
                           ,[NoPrescripcionAsociada]
                           ,[ConTecAsociada]
                           ,[CantTotEntregada]
                           ,[NoLote]
                           ,[ValorEntregado]
                           ,[FecSuministro]
                           ,[EstSuministro]
                           ,[FecAnulacion]
                           )
                     VALUES
                           ('$ID'
                           ,'$IDSuministro'
                           ,'$NoPrescripcion'
                           ,'$TipoTec'
                           ,'$ConTec'
                           ,'$TipoIDPaciente'
                           ,'$NoIDPaciente'
                           ,'$NoEntrega'
                           ,'$UltEntrega'
                           ,'$EntregaCompleta'
                           ,'$CausaNoEntrega'
                           ,'$NoPrescripcionAsociada'
                           ,'$ConTecAsociada'
                           ,'$CantTotEntregada'
                           ,'$NoLote'
                           ,'$ValorEntregado'
                           ,'$FecSuministro'
                           ,'$EstSuministro'
                           ,'$FecAnulacion'
                            )

                  END ELSE BEGIN 


                UPDATE [dbo].[MIPRES_SUMINISTRO]
                   SET [ID] = '$ID'
                      ,[IDSuministro] = '$IDSuministro'
                      ,[NoPrescripcion] = '$NoPrescripcion'
                      ,[TipoTec] = '$TipoTec'
                      ,[ConTec] = '$ConTec'
                      ,[TipoIDPaciente] = '$TipoIDPaciente'
                      ,[NoIDPaciente] = '$NoIDPaciente'
                      ,[NoEntrega] = '$NoEntrega'
                      ,[UltEntrega] = '$UltEntrega'
                      ,[EntregaCompleta] = '$EntregaCompleta'
                      ,[CausaNoEntrega] = '$CausaNoEntrega'
                      ,[NoPrescripcionAsociada] = '$NoPrescripcionAsociada'
                      ,[ConTecAsociada] = '$ConTecAsociada'
                      ,[CantTotEntregada] = '$CantTotEntregada'
                      ,[NoLote] = '$NoLote'
                      ,[ValorEntregado] ='$ValorEntregado'
                      ,[FecSuministro] = '$FecSuministro'
                      ,[EstSuministro] = '$EstSuministro'
                      ,[FecAnulacion] = '$FecAnulacion'
                      ,[FechaIngreso] = getdate()
                 WHERE ID = '$ID' AND IDSuministro = '$IDSuministro'

               END";

        return ejecutarConsulta($sql);
    }

}
