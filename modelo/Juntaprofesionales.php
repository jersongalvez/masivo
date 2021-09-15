<?php

////////////////////////////////////////////////////////////////////////////////
/////////////////////////        MASIVO MIPRES         /////////////////////////
/////////////////////////      PIJAOS SALUD EPSI      //////////////////////////
///////////////////         MODELO JUNTA PROFESIONALES        //////////////////
/////////////////////////  DEPARTAMENTO DE DESARROLLO  /////////////////////////
////////////////////////////////////////////////////////////////////////////////
//Incluimos inicialmete la conexion a la base de datos
require_once '../config/conexion.php';

class Juntaprofesionales {

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
    public function url_Juntaprofesional($regimen, $codurl) {

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
     * Metodo que inserta las juntas de profesionales S y C
     * @param String $NoPrescripcion
     * @param date $FPrescripcion
     * @param String $TipoTecnologia
     * @param String  $Consecutivo 
     * @param String $EstJM
     * @param String $CodEntProc
     * @param String $Observaciones
     * @param String $JustificacionTecnica
     * @param String $Modalidad
     * @param String $NoActa
     * @param date $FechaActa
     * @param date $FProceso
     * @param String $TipoIDPaciente
     * @param String $NroIDPaciente
     * @param String $CodEntJM
     * @return obj
     */
    public function insertar_Juntaprofesional($NoPrescripcion, $FPrescripcion, $TipoTecnologia, $Consecutivo, $EstJM, $CodEntProc,
            $Observaciones, $JustificacionTecnica, $Modalidad, $NoActa, $FechaActa, $FProceso, $TipoIDPaciente, $NroIDPaciente, $CodEntJM) {

        $Modalidad = str_replace(" ' ", "", $Modalidad);
        
        $sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED
                IF NOT EXISTS (SELECT NOPRESCRIPCION FROM MIPRES_JUNTAPROFESIONAL WHERE NOPRESCRIPCION = '$NoPrescripcion' AND TIPTECNOLOGIA = '$TipoTecnologia' AND CONSECUTIVO = '$Consecutivo')
                BEGIN 
                INSERT INTO [dbo].[MIPRES_JUNTAPROFESIONAL]
                ([NOPRESCRIPCION]
                ,[FPRESCRIPCION]
                ,[TIPTECNOLOGIA]
                ,[CONSECUTIVO]
                ,[ESTJM]
                ,[CODENTPROC]
                ,[OBSERVACIONES]
                ,[JUSTIFICACIONTECNICA]
                ,[MODALIDAD]
                ,[NOACTA]
                ,[FECHAACTA]
                ,[FPROCESO]
                ,[TIPOIDPACIENTE]
                ,[NROIDPACIENTE]
                ,[CODENTJM]
                ,[USU_CARGUE]
                ,[FEC_CRUCE])
                VALUES
                ('$NoPrescripcion' 
                ,'$FPrescripcion' 
                ,'$TipoTecnologia'
                ,'$Consecutivo'
                ,'$EstJM'
                ,'$CodEntProc'
                ,'$Observaciones'
                ,'$JustificacionTecnica'
                ,IIF('$Modalidad' = 'NULL', NULL, '$Modalidad')
                ,'$NoActa'
                ,'$FechaActa' 
                ,'$FProceso' 
                ,'$TipoIDPaciente'
                ,'$NroIDPaciente'
                ,'$CodEntJM'
                ,'SA'
                ,CURRENT_TIMESTAMP)
                
                END ELSE BEGIN 
                UPDATE [dbo].[MIPRES_JUNTAPROFESIONAL] WITH (ROWLOCK) SET 
                [NOPRESCRIPCION] = '$NoPrescripcion' 
                ,[FPRESCRIPCION] = '$FPrescripcion'
                ,[TIPTECNOLOGIA] = '$TipoTecnologia'
                ,[CONSECUTIVO] = '$Consecutivo'
                ,[ESTJM] = '$EstJM'
                ,[CODENTPROC] = '$CodEntProc'
                ,[OBSERVACIONES] = '$Observaciones'
                ,[JUSTIFICACIONTECNICA] = '$JustificacionTecnica'
                ,[MODALIDAD] = IIF('$Modalidad' = 'NULL', NULL, '$Modalidad')
                ,[NOACTA] = '$NoActa'
                ,[FECHAACTA] = '$FechaActa'
                ,[FPROCESO] = '$FProceso'
                ,[TIPOIDPACIENTE] = '$TipoIDPaciente'
                ,[NROIDPACIENTE] = '$NroIDPaciente'
                ,[CODENTJM] = '$CodEntJM'
                ,[USU_CARGUE] = 'SA'
                ,[FEC_CRUCE] = CURRENT_TIMESTAMP
                WHERE NOPRESCRIPCION = '$NoPrescripcion' AND TIPTECNOLOGIA = '$TipoTecnologia' AND CONSECUTIVO = '$Consecutivo'
                END";

        
        return ejecutarConsulta($sql);
    }

}
