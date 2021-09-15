<?php

////////////////////////////////////////////////////////////////////////////////
/////////////////////////        MASIVO MIPRES         /////////////////////////
/////////////////////////      PIJAOS SALUD EPSI      //////////////////////////
///////////////////          MODELO REPORTE ENTREGA           //////////////////
/////////////////////////  DEPARTAMENTO DE DESARROLLO  /////////////////////////
////////////////////////////////////////////////////////////////////////////////
//Incluimos inicialmete la conexion a la base de datos
require_once '../config/conexion.php';

class Reporteprov {

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
    public function url_reporteEnt($regimen, $codurl) {

        $sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED "
                . "SELECT T.DES_TEM_TOKEN, U.DES_URL FROM PRS_TEM_TOKEN T, PRS_URL_SERVICES U WITH (NOLOCK) WHERE T.TIP_TEM_TOKEN = '$regimen' AND U.COD_URL = '$codurl'";

        return ejecutarConsulta($sql);
    }

    /**
     * Metodo que inserta o actualiza los reportes de entrega a proveedor
     * @param typeString $ID
     * @param typeString $IDReporteEntrega
     * @param typeString $NoPrescripcion
     * @param typeString $TipoTec
     * @param typeString $ConTec
     * @param typeString $TipoIDPaciente
     * @param typeString $NoIDPaciente
     * @param typeString $NoEntrega
     * @param typeString $EstadoEntrega
     * @param typeString $CausaNoEntrega
     * @param typeString $ValorEntregado
     * @param typeString $CodTecEntregado
     * @param typeString $CantTotEntregada
     * @param typeString $NoLote
     * @param date $FecEntrega
     * @param date $FecRepEntrega
     * @param typeString $EstRepEntrega
     * @param date $FecAnulacion
     * @return obj
     */
    public function insertar_reporteEntrega($ID, $IDReporteEntrega, $NoPrescripcion, $TipoTec, $ConTec, $TipoIDPaciente,
            $NoIDPaciente, $NoEntrega, $EstadoEntrega, $CausaNoEntrega, $ValorEntregado, $CodTecEntregado, $CantTotEntregada,
            $NoLote, $FecEntrega, $FecRepEntrega, $EstRepEntrega, $FecAnulacion) {


        $sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED
                IF NOT EXISTS (SELECT * FROM MIPRES_ENTREGA_PROVEEDOR WHERE ID = '$ID' AND IDReporteEntrega = '$IDReporteEntrega') 
                BEGIN 
                INSERT INTO [dbo].[MIPRES_ENTREGA_PROVEEDOR]
                ([ID]
                ,[IDReporteEntrega]
                ,[NoPrescripcion]
                ,[TipoTec]
                ,[ConTec]
                ,[TipoIDPaciente]
                ,[NoIDPaciente]
                ,[NoEntrega]
                ,[EstadoEntrega]
                ,[CausaNoEntrega]
                ,[ValorEntregado]
                ,[CodTecEntregado]
                ,[CantTotEntregada]
                ,[NoLote]
                ,[FecEntrega]
                ,[FecRepEntrega]
                ,[EstRepEntrega]
                ,[FecAnulacion])
                VALUES
                ('$ID'
                ,'$IDReporteEntrega'
                ,'$NoPrescripcion'
                ,'$TipoTec'
                ,'$ConTec'
                ,'$TipoIDPaciente'
                ,'$NoIDPaciente'
                ,'$NoEntrega'
                ,'$EstadoEntrega'
                ,'$CausaNoEntrega'
                ,'$ValorEntregado'
                ,'$CodTecEntregado'
                ,'$CantTotEntregada'
                ,'$NoLote'
                ,'$FecEntrega'
                ,'$FecRepEntrega'
                ,'$EstRepEntrega'
                ,'$FecAnulacion'
                )
                
                END ELSE BEGIN 
                
              UPDATE [dbo].[MIPRES_ENTREGA_PROVEEDOR] WITH (ROWLOCK)
                 SET [ID] = '$ID'
                    ,[IDReporteEntrega] = '$IDReporteEntrega'
                    ,[NoPrescripcion] = '$NoPrescripcion'
                    ,[TipoTec] = '$TipoTec'
                    ,[ConTec] = '$ConTec'
                    ,[TipoIDPaciente] = '$TipoIDPaciente'
                    ,[NoIDPaciente] = '$NoIDPaciente'
                    ,[NoEntrega] = '$NoEntrega'
                    ,[EstadoEntrega] = '$EstadoEntrega'
                    ,[CausaNoEntrega] = '$CausaNoEntrega'
                    ,[ValorEntregado] = '$ValorEntregado'
                    ,[CodTecEntregado] = '$CodTecEntregado'
                    ,[CantTotEntregada] = '$CantTotEntregada'
                    ,[NoLote] = '$NoLote'
                    ,[FecEntrega] = '$FecEntrega'
                    ,[FecRepEntrega] = '$FecRepEntrega'
                    ,[EstRepEntrega] = '$EstRepEntrega'
                    ,[FecAnulacion] = '$FecAnulacion'
               WHERE ID = '$ID' AND IDReporteEntrega = '$IDReporteEntrega'

                END";

        return ejecutarConsulta($sql);
    }

    /**
     * Metodo que obtiene los reportes de entrega con retroactivos
     * @return obj
     */
    public function get_retroactivo() {

        $sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED
                SELECT DISTINCT NOPRESCRIPCION AS NOPRESCRIPCION FROM MIPRES_ENTREGA_PROVEEDOR
                WHERE ID IN (SELECT ID FROM MIPRES_ENTREGA_PROVEEDOR
                WHERE ESTREPENTREGA <> 0 GROUP BY ID HAVING COUNT(*) > 1)";
        
        return ejecutarConsulta($sql);
    }

}
