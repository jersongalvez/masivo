<?php

////////////////////////////////////////////////////////////////////////////////
/////////////////////////        MASIVO MIPRES         /////////////////////////
/////////////////////////      PIJAOS SALUD EPSI      //////////////////////////
///////////////////            MODELO FACTURACION          /////////////////////
/////////////////////////  DEPARTAMENTO DE DESARROLLO  /////////////////////////
////////////////////////////////////////////////////////////////////////////////
//Incluimos inicialmete la conexion a la base de datos
require_once '../config/conexion.php';

class Facturacion {

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
    public function url_facturacion($regimen, $codurl) {

        $sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED "
                . "SELECT T.DES_TEM_TOKEN, U.DES_URL FROM PRS_TEM_TOKEN T, PRS_URL_SERVICES U WITH (NOLOCK) WHERE T.TIP_TEM_TOKEN = '$regimen' AND U.COD_URL = '$codurl'";

        return ejecutarConsulta($sql);
    }

    /**
     * Metodo que inserta la facturacion S
     * @param String $ID
     * @param String $IDFacturacion
     * @param String $NoPrescripcion
     * @param String $TipoTec
     * @param String $ConTec
     * @param String $TipoIDPaciente
     * @param String $NoIDPaciente
     * @param String $NoEntrega
     * @param String $NoSubEntrega
     * @param String $NoFactura
     * @param String $NoIDEPS
     * @param String $CodEPS
     * @param String $CodSerTecAEntregado
     * @param String $CantUnMinDis
     * @param String $ValorUnitFacturado
     * @param String $ValorTotFacturado
     * @param String $CuotaModer
     * @param String $Copago
     * @param date $FecFacturacion
     * @param String $EstFacturacion
     * @param date $FecAnulacion
     * @return obj
     */
    public function insertar_facturacion($ID, $IDFacturacion, $NoPrescripcion, $TipoTec, $ConTec, $TipoIDPaciente, $NoIDPaciente,
            $NoEntrega, $NoSubEntrega, $NoFactura, $NoIDEPS, $CodEPS, $CodSerTecAEntregado, $CantUnMinDis, $ValorUnitFacturado,
            $ValorTotFacturado, $CuotaModer, $Copago, $FecFacturacion, $EstFacturacion, $FecAnulacion) {

        $sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED
                IF NOT EXISTS (SELECT * FROM MIPRES_FACTURACION WHERE ID = '$ID' AND IDFacturacion = '$IDFacturacion' ) BEGIN
                INSERT INTO [dbo].[MIPRES_FACTURACION]
                           ([ID]
                           ,[IDFacturacion]
                           ,[NoPrescripcion]
                           ,[TipoTec]
                           ,[ConTec]
                           ,[TipoIDPaciente]
                           ,[NoIDPaciente]
                           ,[NoEntrega]
                           ,[NoSubEntrega]
                           ,[NoFactura]
                           ,[NoIDEPS]
                           ,[CodEPS]
                           ,[CodSerTecAEntregado]
                           ,[CantUnMinDis]
                           ,[ValorUnitFacturado]
                           ,[ValorTotFacturado]
                           ,[CuotaModer]
                           ,[Copago]
                           ,[FecFacturacion]
                           ,[EstFacturacion]
                           ,[FecAnulacion])
                     VALUES
                           ('$ID'
                           ,'$IDFacturacion'
                           ,'$NoPrescripcion'
                           ,'$TipoTec'
                           ,'$ConTec'
                           ,'$TipoIDPaciente'
                           ,'$NoIDPaciente'
                           ,'$NoEntrega'
                           ,'$NoSubEntrega'
                           ,'$NoFactura'
                           ,'$NoIDEPS'
                           ,'$CodEPS'
                           ,'$CodSerTecAEntregado'
                           ,'$CantUnMinDis'
                           ,'$ValorUnitFacturado'
                           ,'$ValorTotFacturado'
                           ,'$CuotaModer'
                           ,'$Copago'
                           ,'$FecFacturacion'
                           ,'$EstFacturacion'
                           ,'$FecAnulacion'
                           )
                END ELSE BEGIN 
                UPDATE [dbo].[MIPRES_FACTURACION] WITH (ROWLOCK)
                   SET 
                       [NoPrescripcion] = '$NoPrescripcion'
                      ,[TipoTec] = '$TipoTec'
                      ,[ConTec] = '$ConTec'
                      ,[TipoIDPaciente] = '$TipoIDPaciente'
                      ,[NoIDPaciente] = '$NoIDPaciente'
                      ,[NoEntrega] = '$NoEntrega'
                      ,[NoSubEntrega] = '$NoSubEntrega'
                      ,[NoFactura] = '$NoFactura'
                      ,[NoIDEPS] = '$NoIDEPS'
                      ,[CodEPS] = '$CodEPS'
                      ,[CodSerTecAEntregado] = '$CodSerTecAEntregado'
                      ,[CantUnMinDis] = '$CantUnMinDis'
                      ,[ValorUnitFacturado] = '$ValorUnitFacturado'
                      ,[ValorTotFacturado] = '$ValorTotFacturado'
                      ,[CuotaModer] = '$CuotaModer'
                      ,[Copago] = '$Copago'
                      ,[FecFacturacion] = '$FecFacturacion'
                      ,[EstFacturacion] = '$EstFacturacion'
                      ,[FecAnulacion] = '$FecAnulacion'
                 WHERE ID = '$ID' AND IDFacturacion = '$IDFacturacion'
                END";

        return ejecutarConsulta($sql);
    }

    /**
     * Metodo que obtiene la facturacion con retroactivos
     * @return obj
     */
    public function get_retroactivoFa() {

        $sql = "SELECT DISTINCT NOPRESCRIPCION NOPRESCRIPCION FROM MIPRES_FACTURACION
                WHERE CONCAT(ID,NOSUBENTREGA) IN (SELECT CONCAT(ID,NOSUBENTREGA) FROM MIPRES_FACTURACION 
                                                  WHERE ESTFACTURACION <> 0 GROUP BY CONCAT(ID,NOSUBENTREGA) 
                                                  HAVING COUNT(*) > 1 ) ";

        return ejecutarConsulta($sql);
    }

}
