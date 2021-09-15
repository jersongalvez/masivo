<?php

////////////////////////////////////////////////////////////////////////////////
/////////////////////////        MASIVO MIPRES         /////////////////////////
/////////////////////////      PIJAOS SALUD EPSI      //////////////////////////
///////////////////          MODELO PRESCRIPCIONES            //////////////////
/////////////////////////  DEPARTAMENTO DE DESARROLLO  /////////////////////////
////////////////////////////////////////////////////////////////////////////////
//Incluimos inicialmete la conexion a la base de datos
require_once '../config/conexion.php';

class Prescripciones {

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
    public function url_prescripcion($regimen, $codurl) {

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
     * Metodo que obtiene el idordemitem de un afiliado
     * @param String $ti
     * @param String $ni
     * @return String
     */
    public function IDORDENITEM($ti, $ni) {

        $sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED "
                . "SELECT TOP 1 IDORDENITEM AS ID FROM AFILIADOSSUB WHERE TIP_DOCUMENTO_BEN = '$ti' AND NUM_DOCUMENTO_BEN = '$ni' AND EST_AFILIADO = '1' ";

        $salida = sqlsrv_fetch_object(ejecutarConsulta($sql));
        $retorno = ($salida) ? $salida->ID : '';

        return $retorno;
    }

    /**
     * Metodo que inserta las prescripciones S y C
     * @param String $NoPrescripcion
     * @param date $FPrescripcion
     * @param String $HPrescripcion
     * @param String $CodHabIPS
     * @param String $TipoIDIPS
     * @param String $NroIDIPS
     * @param String $CodDANEMunIPS
     * @param String $DirSedeIPS
     * @param String $TelSedeIPS
     * @param String $TipoIDProf
     * @param String $NumIDProf
     * @param String $PNProfS
     * @param String $SNProfS
     * @param String $PAProfS
     * @param String $SAProfS
     * @param String $RegProfS
     * @param String $TipoIDPaciente
     * @param String $NroIDPaciente
     * @param String $PNPaciente
     * @param String $SNPaciente
     * @param String $PAPaciente
     * @param String $SAPaciente
     * @param String $CodAmbAte
     * @param String $EnfHuerfana
     * @param String $CodEnfHuerfana
     * @param String $CodDxPpal
     * @param String $CodDxRel1
     * @param String $CodDxRel2
     * @param String $SopNutricional
     * @param String $CodEPS
     * @param String $TipoIDMadrePaciente
     * @param String $NroIDMadrePaciente
     * @param String $TipoTransc
     * @param String $TipoIDDonanteVivo
     * @param String $NroIDDonanteVivo
     * @param String $EstPres
     * @param String $IDORDENITEM
     * @return obj
     */
    public function insertar_prescripcion($NoPrescripcion, $FPrescripcion, $HPrescripcion, $CodHabIPS, $TipoIDIPS, $NroIDIPS, $CodDANEMunIPS,
            $DirSedeIPS, $TelSedeIPS, $TipoIDProf, $NumIDProf, $PNProfS, $SNProfS, $PAProfS, $SAProfS, $RegProfS, $TipoIDPaciente, $NroIDPaciente,
            $PNPaciente, $SNPaciente, $PAPaciente, $SAPaciente, $CodAmbAte, $EnfHuerfana, $CodEnfHuerfana, $CodDxPpal, $CodDxRel1, $CodDxRel2, $SopNutricional,
            $CodEPS, $TipoIDMadrePaciente, $NroIDMadrePaciente, $TipoTransc, $TipoIDDonanteVivo, $NroIDDonanteVivo, $EstPres, $IDORDENITEM) {


        $sql = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED
                        IF NOT EXISTS (SELECT NOPRESCRIPCION FROM MIPRES_PRESCRIPCION WHERE NOPRESCRIPCION = '$NoPrescripcion' )
                        BEGIN 
                        INSERT INTO [dbo].[MIPRES_PRESCRIPCION]
                        ([NOPRESCRIPCION]
                        ,[FPRESCRIPCION]
                        ,[HPRESCRIPCION]
                        ,[CODHABIPS]
                        ,[TIPOIDIPS]
                        ,[NROIDIPS]
                        ,[CODDANEMUNIPS]
                        ,[DIRSEDEIPS]
                        ,[TELSEDEIPS]
                        ,[TIPOIDPROF]
                        ,[NUMIDPROF]
                        ,[PNPROFS]
                        ,[SNPROFS]
                        ,[PAPROFS]
                        ,[SAPROFS]
                        ,[REGPROFS]
                        ,[TIPOIDPACIENTE]
                        ,[NROIDPACIENTE]
                        ,[PNPACIENTE]
                        ,[SNPACIENTE]
                        ,[PAPACIENTE]
                        ,[SAPACIENTE]
                        ,[CODAMBATE]
                        ,[ENFHUERFANA]
                        ,[CODENFHUERFANA]
                        ,[CODDXPPAL]
                        ,[CODDXREL1]
                        ,[CODDXREL2]
                        ,[SOPNUTRICIONAL]
                        ,[CODEPS]
                        ,[TIPOIDMADREPACIENTE]
                        ,[NROIDMADREPACIENTE]
                        ,[TIPOTRANSC]
                        ,[TIPOIDDONANTEVIVO]
                        ,[NROIDDONANTEVIVO]
                        ,[ESTPRES]
                        ,[MIDORDENITEM]
                        ,[USU_CARGUE]
                        ,[FEC_CRUCE]
                        ,[REGIMEN]
                        ,REPORTMIPRES)
                        VALUES
                        ('$NoPrescripcion'
                        ,'$FPrescripcion'
                        ,'$HPrescripcion'
                        ,IIF('$CodHabIPS' = 'NULL', NULL, '$CodHabIPS')
                        ,'$TipoIDIPS'
                        ,'$NroIDIPS'
                        ,'$CodDANEMunIPS'
                        ,'$DirSedeIPS'
                        ,'$TelSedeIPS'
                        ,'$TipoIDProf'
                        ,IIF('$NumIDProf' = 'NULL', NULL, '$NumIDProf')
                        ,'$PNProfS'
                        ,'$SNProfS'
                        ,'$PAProfS'
                        ,'$SAProfS'
                        ,'$RegProfS'
                        ,'$TipoIDPaciente'
                        ,'$NroIDPaciente'
                        ,'$PNPaciente'
                        ,'$SNPaciente'
                        ,'$PAPaciente'
                        ,'$SAPaciente'
                        ,IIF('$CodAmbAte' = 'NULL', NULL, '$CodAmbAte')
                        ,IIF('$EnfHuerfana' = 'NULL', NULL, '$EnfHuerfana')
                        ,IIF('$CodEnfHuerfana' = 'NULL', NULL, '$CodEnfHuerfana')
                        ,'$CodDxPpal'
                        ,'$CodDxRel1'
                        ,'$CodDxRel2'
                        ,IIF('$SopNutricional' = 'NULL', NULL, '$SopNutricional')
                        ,'$CodEPS'
                        ,'$TipoIDMadrePaciente'
                        ,'$NroIDMadrePaciente'
                        ,IIF('$TipoTransc' = 'NULL', NULL, '$TipoTransc')
                        ,'$TipoIDDonanteVivo'
                        ,'$NroIDDonanteVivo'
                        ,IIF('$EstPres' = 'NULL', NULL, '$EstPres')
                        ,IIF('$IDORDENITEM' = 'NULL', NULL, '$IDORDENITEM')
                        ,'SA'
                        ,CURRENT_TIMESTAMP
                        ,'S'
                        ,'NOPRESCRIPCION')
                        END ELSE BEGIN 
                        UPDATE [dbo].[MIPRES_PRESCRIPCION] WITH (ROWLOCK) 
                        SET [NOPRESCRIPCION] = '$NoPrescripcion'
                        ,[FPRESCRIPCION] = '$FPrescripcion'
                        ,[HPRESCRIPCION] = '$HPrescripcion'
                        ,[CODHABIPS] = IIF('$CodHabIPS' = 'NULL', NULL, '$CodHabIPS')
                        ,[TIPOIDIPS] = '$TipoIDIPS'
                        ,[NROIDIPS] = '$NroIDIPS'
                        ,[CODDANEMUNIPS] = '$CodDANEMunIPS'
                        ,[DIRSEDEIPS] = '$DirSedeIPS'
                        ,[TELSEDEIPS] = '$TelSedeIPS'
                        ,[TIPOIDPROF] = '$TipoIDProf'
                        ,[NUMIDPROF] = IIF('$NumIDProf' = 'NULL', NULL, '$NumIDProf')
                        ,[PNPROFS] = '$PNProfS'
                        ,[SNPROFS] = '$SNProfS'
                        ,[PAPROFS] = '$PAProfS'
                        ,[SAPROFS] = '$SAProfS'
                        ,[REGPROFS] = '$RegProfS'
                        ,[TIPOIDPACIENTE] = '$TipoIDPaciente'
                        ,[NROIDPACIENTE] = '$NroIDPaciente'
                        ,[PNPACIENTE] = '$PNPaciente'
                        ,[SNPACIENTE] = '$SNPaciente'
                        ,[PAPACIENTE] = '$PAPaciente'
                        ,[SAPACIENTE] = '$SAPaciente'
                        ,[CODAMBATE] = IIF('$CodAmbAte' = 'NULL', NULL, '$CodAmbAte')
                        ,[ENFHUERFANA] = IIF('$EnfHuerfana' = 'NULL', NULL, '$EnfHuerfana')
                        ,[CODENFHUERFANA] = IIF('$CodEnfHuerfana' = 'NULL', NULL, '$CodEnfHuerfana')
                        ,[CODDXPPAL] = '$CodDxPpal'
                        ,[CODDXREL1] = '$CodDxRel1'
                        ,[CODDXREL2] = '$CodDxRel2'
                        ,[SOPNUTRICIONAL] = IIF('$SopNutricional' = 'NULL', NULL, '$SopNutricional')
                        ,[CODEPS] = '$CodEPS'
                        ,[TIPOIDMADREPACIENTE] = '$TipoIDMadrePaciente'
                        ,[NROIDMADREPACIENTE] = '$NroIDMadrePaciente'
                        ,[TIPOTRANSC] = IIF('$TipoTransc' = 'NULL', NULL, '$TipoTransc')
                        ,[TIPOIDDONANTEVIVO] = '$TipoIDDonanteVivo'
                        ,[NROIDDONANTEVIVO] = '$NroIDDonanteVivo'
                        ,[ESTPRES] = IIF('$EstPres' = 'NULL', NULL, '$EstPres')
                        ,[MIDORDENITEM] = IIF('$IDORDENITEM' = 'NULL', NULL, '$IDORDENITEM')
                        ,[USU_CARGUE] = 'SA'
                        ,[FEC_CRUCE] = CURRENT_TIMESTAMP
                        ,[REGIMEN] = 'S'
                        WHERE NOPRESCRIPCION = '$NoPrescripcion'
                    END";


        return ejecutarConsulta($sql);
    }

    /**
     * Metodo que inserta los procedimientos S y C
     * @param String $NoPrescripcion
     * @param String $ConOrden
     * @param String $TipoPrest
     * @param String $CausaS11
     * @param String $CausaS12
     * @param String $CausaS2
     * @param String $CausaS3
     * @param String $CausaS4
     * @param String $ProPBSUtilizado
     * @param String $CausaS5
     * @param String $ProPBSDescartado
     * @param String $RznCausaS51
     * @param String $DescRzn51
     * @param String $RznCausaS52
     * @param String $DescRzn52
     * @param String $CausaS6
     * @param String $CausaS7
     * @param String $CodCUPS
     * @param String $CanForm
     * @param String $CodFreUso
     * @param String $Cant
     * @param String $CodPerDurTrat
     * @param String $JustNoPBS
     * @param String $IndRec
     * @param String $EstJM
     * @param String $CantTotal
     * @return obj
     */
    public function insertar_procedimiento($NoPrescripcion, $ConOrden, $TipoPrest, $CausaS11, $CausaS12, $CausaS2, $CausaS3, $CausaS4,
            $ProPBSUtilizado, $CausaS5, $ProPBSDescartado, $RznCausaS51, $DescRzn51, $RznCausaS52, $DescRzn52, $CausaS6, $CausaS7, $CodCUPS,
            $CanForm, $CodFreUso, $Cant, $CodPerDurTrat, $JustNoPBS, $IndRec, $EstJM, $CantTotal) {


        $sql_procedimientos = " SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED
                                IF NOT EXISTS (SELECT NOPRESCRIPCION FROM [MIPRES_PROCEDIMIENTOS] WHERE NOPRESCRIPCION = '$NoPrescripcion' AND CONORDEN = '$ConOrden')
                                BEGIN 
                                INSERT INTO [dbo].[MIPRES_PROCEDIMIENTOS]
                                ([NOPRESCRIPCION]
                                ,[CONORDEN]
                                ,[TIPOPREST]
                                ,[CAUSAS11]
                                ,[CAUSAS12]
                                ,[CAUSAS2]
                                ,[CAUSAS3]
                                ,[CAUSAS4]
                                ,[PROPBSUTILIZADO]
                                ,[CAUSAS5]
                                ,[PROPBSDESCARTADO]
                                ,[RZNCAUSAS51]
                                ,[DESCRZN51]
                                ,[RZNCAUSAS52]
                                ,[DESCRZN52]
                                ,[CAUSAS6]
                                ,[CAUSAS7]
                                ,[CODCUPS]
                                ,[CANFORM]
                                ,[CODFREUSO]
                                ,[CANT] 
                                ,[CODPERDURTRAT]
                                ,[JUSTNOPBS]
                                ,[INDREC]
                                ,[ESTJM]
                                ,[CANTTOTAL] )
                                VALUES
                                ('$NoPrescripcion'
                                ,IIF('$ConOrden' = 'NULL', NULL, '$ConOrden')
                                ,IIF('$TipoPrest' = 'NULL', NULL, '$TipoPrest')
                                ,IIF('$CausaS11' = 'NULL', NULL, '$CausaS11')
                                ,IIF('$CausaS12' = 'NULL', NULL, '$CausaS12')
                                ,IIF('$CausaS2' = 'NULL', NULL, '$CausaS2')
                                ,IIF('$CausaS3' = 'NULL', NULL, '$CausaS3')
                                ,IIF('$CausaS4' = 'NULL', NULL, '$CausaS4')
                                ,'$ProPBSUtilizado'
                                ,IIF('$CausaS5' = 'NULL', NULL, '$CausaS5')
                                ,'$ProPBSDescartado'
                                ,IIF('$RznCausaS51' = 'NULL', NULL, '$RznCausaS51')
                                ,'$DescRzn51'
                                ,IIF('$RznCausaS52' = 'NULL', NULL, '$RznCausaS52')
                                ,'$DescRzn52'
                                ,IIF('$CausaS6' = 'NULL', NULL, '$CausaS6')
                                ,IIF('$CausaS7' = 'NULL', NULL, '$CausaS7')
                                ,'$CodCUPS'
                                ,IIF('$CanForm' = 'NULL', NULL, '$CanForm')
                                ,IIF('$CodFreUso' = 'NULL', NULL, '$CodFreUso')
                                ,IIF('$Cant' = 'NULL', NULL, '$Cant')
                                ,IIF('$CodPerDurTrat' = 'NULL', NULL, '$CodPerDurTrat')
                                ,'$JustNoPBS'
                                ,'$IndRec'
                                ,IIF('$EstJM' = 'NULL', NULL, '$EstJM')
                                ,IIF('$CantTotal' = 'NULL', NULL, '$CantTotal')
                                )

                                END ELSE BEGIN 

                                UPDATE [dbo].[MIPRES_PROCEDIMIENTOS] WITH (ROWLOCK)
                                SET [NOPRESCRIPCION] = '$NoPrescripcion'
                                ,[CONORDEN] = IIF('$ConOrden' = 'NULL', NULL, '$ConOrden')
                                ,[TIPOPREST] = IIF('$TipoPrest' = 'NULL', NULL, '$TipoPrest')
                                ,[CAUSAS11] = IIF('$CausaS11' = 'NULL', NULL, '$CausaS11')
                                ,[CAUSAS12] = IIF('$CausaS12' = 'NULL', NULL, '$CausaS12')
                                ,[CAUSAS2] = IIF('$CausaS2' = 'NULL', NULL, '$CausaS2')
                                ,[CAUSAS3] = IIF('$CausaS3' = 'NULL', NULL, '$CausaS3')
                                ,[CAUSAS4] = IIF('$CausaS4' = 'NULL', NULL, '$CausaS4')
                                ,[PROPBSUTILIZADO] = '$ProPBSUtilizado'
                                ,[CAUSAS5] = IIF('$CausaS5' = 'NULL', NULL, '$CausaS5')
                                ,[PROPBSDESCARTADO] = '$ProPBSDescartado'
                                ,[RZNCAUSAS51] = IIF('$RznCausaS51' = 'NULL', NULL, '$RznCausaS51')
                                ,[DESCRZN51] = '$DescRzn51'
                                ,[RZNCAUSAS52] = IIF('$RznCausaS52' = 'NULL', NULL, '$RznCausaS52')
                                ,[DESCRZN52] = '$DescRzn52'
                                ,[CAUSAS6] = IIF('$CausaS6' = 'NULL', NULL, '$CausaS6')
                                ,[CAUSAS7] = IIF('$CausaS7' = 'NULL', NULL, '$CausaS7')
                                ,[CODCUPS] = '$CodCUPS'
                                ,[CANFORM] = IIF('$CanForm' = 'NULL', NULL, '$CanForm')
                                ,[CODFREUSO] = IIF('$CodFreUso' = 'NULL', NULL, '$CodFreUso')
                                ,[CANT] = IIF('$Cant' = 'NULL', NULL, '$Cant')
                                ,[CODPERDURTRAT] = IIF('$CodPerDurTrat' = 'NULL', NULL, '$CodPerDurTrat')
                                ,[JUSTNOPBS] = '$JustNoPBS'
                                ,[INDREC] = '$IndRec'
                                ,[ESTJM] = IIF('$EstJM' = 'NULL', NULL, '$EstJM')
                                ,[CANTTOTAL] = IIF('$CantTotal' = 'NULL', NULL, '$CantTotal')
                                WHERE NOPRESCRIPCION = '$NoPrescripcion' AND CONORDEN = '$ConOrden'
                            END";


        return ejecutarConsulta($sql_procedimientos);
    }

    /**
     * Metodo que inserta los productos nutricionales S y C
     * @param String $NoPrescripcion
     * @param String $ConOrden
     * @param String $TipoPrest
     * @param String $CausaS1
     * @param String $CausaS2
     * @param String $CausaS3
     * @param String $CausaS4
     * @param String $ProNutUtilizado
     * @param String $RznCausaS41
     * @param String $DescRzn41
     * @param String $RznCausaS42
     * @param String $DescRzn42
     * @param String $CausaS5
     * @param String $ProNutDescartado
     * @param String $RznCausaS51
     * @param String $DescRzn51
     * @param String $RznCausaS52
     * @param String $DescRzn52
     * @param String $RznCausaS53
     * @param String $DescRzn53
     * @param String $RznCausaS54
     * @param String $DescRzn54
     * @param String $TippProNut
     * @param String $DescProdNutr
     * @param String $CodForma
     * @param String $CodViaAdmon
     * @param String $JustNoPBS
     * @param String $Dosis
     * @param String $DosisUM
     * @param String $NoFAdmon
     * @param String $CodFreAdmon
     * @param String $CanTrat
     * @param String $DurTrat
     * @param String $CantTotalF
     * @param String $UFCantTotal
     * @param String $IndRec
     * @param String $NoPrescAso
     * @param String $EstJM
     * @param String $DXEnfHuer
     * @param String $DXVIH
     * @param String $DXCaPal
     * @param String $DXEnfRCEV
     * @param String $IndEsp
     * @return obj
     */
    public function insertar_pronutricional($NoPrescripcion, $ConOrden, $TipoPrest, $CausaS1, $CausaS2, $CausaS3, $CausaS4,
            $ProNutUtilizado, $RznCausaS41, $DescRzn41, $RznCausaS42, $DescRzn42, $CausaS5, $ProNutDescartado, $RznCausaS51,
            $DescRzn51, $RznCausaS52, $DescRzn52, $RznCausaS53, $DescRzn53, $RznCausaS54, $DescRzn54, $TippProNut, $DescProdNutr,
            $CodForma, $CodViaAdmon, $JustNoPBS, $Dosis, $DosisUM, $NoFAdmon, $CodFreAdmon, $CanTrat, $DurTrat, $CantTotalF,
            $UFCantTotal, $IndRec, $NoPrescAso, $EstJM, $DXEnfHuer, $DXVIH, $DXCaPal, $DXEnfRCEV, $IndEsp) {


        $sql_nutricionales = " SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED
                                IF NOT EXISTS (SELECT NOPRESCRIPCION FROM [MIPRES_NUTRICIONALES] WHERE NOPRESCRIPCION = '$NoPrescripcion' AND CONORDEN = '$ConOrden')
                                BEGIN 
                                INSERT INTO [dbo].[MIPRES_NUTRICIONALES]
                                ([NOPRESCRIPCION]
                                ,[CONORDEN]
                                ,[TIPOPREST]
                                ,[CAUSAS1]
                                ,[CAUSAS2]
                                ,[CAUSAS3]
                                ,[CAUSAS4]
                                ,[PRONUTUTILIZADO]
                                ,[RZNCAUSAS41]
                                ,[DESCRZN41]
                                ,[RZNCAUSAS42]
                                ,[DESCRZN42]
                                ,[CAUSAS5]
                                ,[PRONUTDESCARTADO]
                                ,[RZNCAUSAS51]
                                ,[DESCRZN51]
                                ,[RZNCAUSAS52]
                                ,[DESCRZN52]
                                ,[RZNCAUSAS53]
                                ,[DESCRZN53]
                                ,[RZNCAUSAS54]
                                ,[DESCRZN54]
                                ,[TIPPPRONUT]
                                ,[DESCPRODNUTR]
                                ,[CODFORMA]
                                ,[CODVIAADMON]
                                ,[JUSTNOPBS]
                                ,[DOSIS]
                                ,[DOSISUM]
                                ,[NOFADMON]
                                ,[CODFREADMON]
                                ,[CANTRAT]
                                ,[DURTRAT]
                                ,[CANTTOTALF]
                                ,[UFCANTTOTAL]
                                ,[INDREC]
                                ,[NOPRESCASO]
                                ,[ESTJM]
                                ,[DXENFHUER]
                                ,[DXVIH]
                                ,[DXCAPAL]
                                ,[DXENFRCEV]
                                ,[INDESP])
                                VALUES
                                ('$NoPrescripcion'
                                ,IIF('$ConOrden' = 'NULL', NULL, '$ConOrden')
                                ,IIF('$TipoPrest' = 'NULL', NULL, '$TipoPrest')
                                ,IIF('$CausaS1' = 'NULL', NULL, '$CausaS1')
                                ,IIF('$CausaS2' = 'NULL', NULL, '$CausaS2')
                                ,IIF('$CausaS3' = 'NULL', NULL, '$CausaS3')
                                ,IIF('$CausaS4' = 'NULL', NULL, '$CausaS4')
                                ,'$ProNutUtilizado'
                                ,IIF('$RznCausaS41' = 'NULL', NULL, '$RznCausaS41')
                                ,'$DescRzn41'
                                ,IIF('$RznCausaS42' = 'NULL', NULL, '$RznCausaS42')
                                ,'$DescRzn42'
                                ,IIF('$CausaS5' = 'NULL', NULL, '$CausaS5')
                                ,'$ProNutDescartado'
                                ,IIF('$RznCausaS51' = 'NULL', NULL, '$RznCausaS51')
                                ,'$DescRzn51'
                                ,IIF('$RznCausaS52' = 'NULL', NULL, '$RznCausaS52')
                                ,'$DescRzn52'
                                ,IIF('$RznCausaS53' = 'NULL', NULL, '$RznCausaS53')
                                ,'$DescRzn53'
                                ,IIF('$RznCausaS54' = 'NULL', NULL, '$RznCausaS54')
                                ,'$DescRzn54'
                                ,IIF('$TippProNut' = 'NULL', NULL, '$TippProNut')
                                ,IIF('$DescProdNutr' = 'NULL', NULL, '$DescProdNutr')
                                ,IIF('$CodForma' = 'NULL', NULL, '$CodForma')
                                ,IIF('$CodViaAdmon' = 'NULL', NULL, '$CodViaAdmon')
                                ,'$JustNoPBS'
                                ,IIF('$Dosis' = 'NULL', NULL, '$Dosis')
                                ,'$DosisUM'
                                ,IIF('$NoFAdmon' = 'NULL', NULL, '$NoFAdmon')
                                ,IIF('$CodFreAdmon' = 'NULL', NULL, '$CodFreAdmon')
                                ,IIF('$CanTrat' = 'NULL', NULL, '$CanTrat')
                                ,IIF('$DurTrat' = 'NULL', NULL, '$DurTrat')
                                ,IIF('$CantTotalF' = 'NULL', NULL, '$CantTotalF')
                                ,IIF('$UFCantTotal' = 'NULL', NULL, '$UFCantTotal')
                                ,'$IndRec'
                                ,'$NoPrescAso'
                                ,IIF('$EstJM' = 'NULL', NULL, '$EstJM')
                                ,'$DXEnfHuer'
                                ,'$DXVIH'
                                ,'$DXCaPal'
                                ,'$DXEnfRCEV'
                                ,IIF('$IndEsp' = 'NULL', NULL, '$IndEsp')
                                )

                                END ELSE BEGIN 

                                UPDATE [dbo].[MIPRES_NUTRICIONALES] WITH (ROWLOCK)
                                SET [NOPRESCRIPCION] = '$NoPrescripcion'
                                ,[CONORDEN] = IIF('$ConOrden' = 'NULL', NULL, '$ConOrden')
                                ,[TIPOPREST] = IIF('$TipoPrest' = 'NULL', NULL, '$TipoPrest')
                                ,[CAUSAS1] = IIF('$CausaS1' = 'NULL', NULL, '$CausaS1')
                                ,[CAUSAS2] = IIF('$CausaS2' = 'NULL', NULL, '$CausaS2')
                                ,[CAUSAS3] = IIF('$CausaS3' = 'NULL', NULL, '$CausaS3')
                                ,[CAUSAS4] = IIF('$CausaS4' = 'NULL', NULL, '$CausaS4')
                                ,[PRONUTUTILIZADO] = '$ProNutUtilizado'
                                ,[RZNCAUSAS41] = IIF('$RznCausaS41' = 'NULL', NULL, '$RznCausaS41')
                                ,[DESCRZN41] = '$DescRzn41'
                                ,[RZNCAUSAS42] = IIF('$RznCausaS42' = 'NULL', NULL, '$RznCausaS42')
                                ,[DESCRZN42] = '$DescRzn42'
                                ,[CAUSAS5] = IIF('$CausaS5' = 'NULL', NULL, '$CausaS5')
                                ,[PRONUTDESCARTADO]='$ProNutDescartado'
                                ,[RZNCAUSAS51] = IIF('$RznCausaS51' = 'NULL', NULL, '$RznCausaS51')
                                ,[DESCRZN51] = '$DescRzn51'
                                ,[RZNCAUSAS52] = IIF('$RznCausaS52' = 'NULL', NULL, '$RznCausaS52')
                                ,[DESCRZN52] = '$DescRzn52'
                                ,[RZNCAUSAS53] = IIF('$RznCausaS53' = 'NULL', NULL, '$RznCausaS53')
                                ,[DESCRZN53] = '$DescRzn53'
                                ,[RZNCAUSAS54] = IIF('$RznCausaS54' = 'NULL', NULL, '$RznCausaS54')
                                ,[DESCRZN54] = '$DescRzn54'
                                ,[TIPPPRONUT] = IIF('$TippProNut' = 'NULL', NULL, '$TippProNut')
                                ,[DESCPRODNUTR] = IIF('$DescProdNutr' = 'NULL', NULL, '$DescProdNutr')
                                ,[CODFORMA] = IIF('$CodForma' = 'NULL', NULL, '$CodForma')
                                ,[CODVIAADMON] = IIF('$CodViaAdmon' = 'NULL', NULL, '$CodViaAdmon')
                                ,[JUSTNOPBS] = '$JustNoPBS'
                                ,[DOSIS] = IIF('$Dosis' = 'NULL', NULL, '$Dosis')
                                ,[DOSISUM] = '$DosisUM'
                                ,[NOFADMON] = IIF('$NoFAdmon' = 'NULL', NULL, '$NoFAdmon')
                                ,[CODFREADMON] = IIF('$CodFreAdmon' = 'NULL', NULL, '$CodFreAdmon')
                                ,[CANTRAT] = IIF('$CanTrat' = 'NULL', NULL, '$CanTrat')
                                ,[DURTRAT] = IIF('$DurTrat' = 'NULL', NULL, '$DurTrat')
                                ,[CANTTOTALF] = IIF('$CantTotalF' = 'NULL', NULL, '$CantTotalF')
                                ,[UFCANTTOTAL] = IIF('$UFCantTotal' = 'NULL', NULL, '$UFCantTotal')
                                ,[INDREC] = '$IndRec'
                                ,[NOPRESCASO] = '$NoPrescAso'
                                ,[ESTJM] = IIF('$EstJM' = 'NULL', NULL, '$EstJM')
                                ,[DXENFHUER] = '$DXEnfHuer'
                                ,[DXVIH] = '$DXVIH'
                                ,[DXCAPAL] = '$DXCaPal'
                                ,[DXENFRCEV] = '$DXEnfRCEV'
                                ,[INDESP] = IIF('$IndEsp' = 'NULL', NULL, '$IndEsp')
                                WHERE NOPRESCRIPCION = '$NoPrescripcion' AND CONORDEN = '$ConOrden'
                                END";

        return ejecutarConsulta($sql_nutricionales);
    }

    /**
     * Metodo que inserta los servicios complementarios S y C
     * @param String $NoPrescripcion
     * @param String $ConOrden
     * @param String $TipoPrest
     * @param String $CausaS1
     * @param String $CausaS2
     * @param String $CausaS3
     * @param String $CausaS4
     * @param String $DescCausaS4
     * @param String $CausaS5
     * @param String $CodSerComp
     * @param String $DescSerComp
     * @param String $CanForm
     * @param String $CodFreUso
     * @param String $Cant
     * @param String $CodPerDurTrat
     * @param String $JustNoPBS
     * @param String $IndRec
     * @param String $EstJM
     * @param String $CantTotal
     * @return obj
     */
    public function insertar_servcomplementario($NoPrescripcion, $ConOrden, $TipoPrest, $CausaS1, $CausaS2, $CausaS3, $CausaS4,
            $DescCausaS4, $CausaS5, $CodSerComp, $DescSerComp, $CanForm, $CodFreUso, $Cant, $CodPerDurTrat, $JustNoPBS, $IndRec,
            $EstJM, $CantTotal) {


        $sql_complementarios = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED
                                IF NOT EXISTS (SELECT NOPRESCRIPCION FROM [MIPRES_COMPLEMENTARIOS] WHERE NOPRESCRIPCION = '$NoPrescripcion' AND CONORDEN = '$ConOrden')
                                BEGIN 
                                INSERT INTO [dbo].[MIPRES_COMPLEMENTARIOS]
                                ([NOPRESCRIPCION]
                                ,[CONORDEN]
                                ,[TIPOPREST]
                                ,[CAUSAS1]
                                ,[CAUSAS2]
                                ,[CAUSAS3]
                                ,[CAUSAS4]
                                ,[DESCCAUSAS4]
                                ,[CAUSAS5]
                                ,[CODSERCOMP]
                                ,[DESCSERCOMP]
                                ,[CANFORM]
                                ,[CODFREUSO]
                                ,[CANT]
                                ,[CODPERDURTRAT]
                                ,[JUSTNOPBS]
                                ,[INDREC]
                                ,[ESTJM]
                                ,[CANTTOTAL])
                                VALUES
                                ('$NoPrescripcion'
                                ,IIF('$ConOrden' = 'NULL', NULL, '$ConOrden')
                                ,IIF('$TipoPrest' = 'NULL', NULL, '$TipoPrest')
                                ,IIF('$CausaS1' = 'NULL', NULL, '$CausaS1')
                                ,IIF('$CausaS2' = 'NULL', NULL, '$CausaS2')
                                ,IIF('$CausaS3' = 'NULL', NULL, '$CausaS3')
                                ,IIF('$CausaS4' = 'NULL', NULL, '$CausaS4')
                                ,'$DescCausaS4'
                                ,IIF('$CausaS5' = 'NULL', NULL, '$CausaS5')
                                ,IIF('$CodSerComp' = 'NULL', NULL, '$CodSerComp')
                                ,'$DescSerComp'
                                ,IIF('$CanForm' = 'NULL', NULL, '$CanForm')
                                ,IIF('$CodFreUso' = 'NULL', NULL, '$CodFreUso')
                                ,IIF('$Cant' = 'NULL', NULL, '$Cant')
                                ,IIF('$CodPerDurTrat' = 'NULL', NULL, '$CodPerDurTrat')
                                ,'$JustNoPBS'
                                ,'$IndRec'
                                ,IIF('$EstJM' = 'NULL', NULL, '$EstJM')
                                ,IIF('$CantTotal' = 'NULL', NULL, '$CantTotal')
                                ) 
                                END ELSE BEGIN 
                                UPDATE [dbo].[MIPRES_COMPLEMENTARIOS] WITH (ROWLOCK)
                                SET [NOPRESCRIPCION] = '$NoPrescripcion'
                                ,[CONORDEN] = IIF('$ConOrden' = 'NULL', NULL, '$ConOrden')
                                ,[TIPOPREST] = IIF('$TipoPrest' = 'NULL', NULL, '$TipoPrest')
                                ,[CAUSAS1] = IIF('$CausaS1' = 'NULL', NULL, '$CausaS1')
                                ,[CAUSAS2] = IIF('$CausaS2' = 'NULL', NULL, '$CausaS2')
                                ,[CAUSAS3] = IIF('$CausaS3' = 'NULL', NULL, '$CausaS3')
                                ,[CAUSAS4] = IIF('$CausaS4' = 'NULL', NULL, '$CausaS4')
                                ,[DESCCAUSAS4] = '$DescCausaS4'
                                ,[CAUSAS5] = IIF('$CausaS5' = 'NULL', NULL, '$CausaS5')
                                ,[CODSERCOMP] = IIF('$CodSerComp' = 'NULL', NULL, '$CodSerComp')
                                ,[DESCSERCOMP] = '$DescSerComp'
                                ,[CANFORM] = IIF('$CanForm' = 'NULL', NULL, '$CanForm')
                                ,[CODFREUSO] = IIF('$CodFreUso' = 'NULL', NULL, '$CodFreUso')
                                ,[CANT] = IIF('$Cant' = 'NULL', NULL, '$Cant')
                                ,[CODPERDURTRAT] = IIF('$CodPerDurTrat' = 'NULL', NULL, '$CodPerDurTrat')
                                ,[JUSTNOPBS] = '$JustNoPBS'
                                ,[INDREC] = '$IndRec'
                                ,[ESTJM] = IIF('$EstJM' = 'NULL', NULL, '$EstJM')
                                ,[CANTTOTAL] = IIF('$CantTotal' = 'NULL', NULL, '$CantTotal')
                                WHERE NOPRESCRIPCION = '$NoPrescripcion' AND CONORDEN = '$ConOrden'
                                END";

        return ejecutarConsulta($sql_complementarios);
    }

    /**
     * Metodo que inserta los dispositivos S y C
     * @param String $NoPrescripcion
     * @param String $ConOrden
     * @param String $TipoPrest
     * @param String $CausaS1
     * @param String $CodDisp
     * @param String $CanForm
     * @param String $CodFreUso
     * @param String $Cant
     * @param String $CodPerDurTrat
     * @param String $JustNoPBS
     * @param String $IndRec
     * @param String $EstJM
     * @param String $CantTotal
     * @return obj
     */
    public function insertar_dispositivo($NoPrescripcion, $ConOrden, $TipoPrest, $CausaS1, $CodDisp, $CanForm, $CodFreUso, $Cant,
            $CodPerDurTrat, $JustNoPBS, $IndRec, $EstJM, $CantTotal) {

        $sql_dispositivos = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED
                            IF NOT EXISTS (SELECT NOPRESCRIPCION FROM [MIPRES_DISPOSITIVOS] WHERE NOPRESCRIPCION = '$NoPrescripcion' AND CONORDEN = '$ConOrden')
                            BEGIN 
                            INSERT INTO [dbo].[MIPRES_DISPOSITIVOS]
                            ([NOPRESCRIPCION]
                            ,[CONORDEN]
                            ,[TIPOPREST]
                            ,[CAUSAS1]
                            ,[CODDISP]
                            ,[CANFORM]
                            ,[CODFREUSO]
                            ,[CANT]
                            ,[CODPERDURTRAT]
                            ,[JUSTNOPBS]
                            ,[INDREC]
                            ,[ESTJM]
                            ,[CANTTOTAL])
                            VALUES
                            ('$NoPrescripcion'
                            ,IIF('$ConOrden' = 'NULL', NULL, '$ConOrden')
                            ,IIF('$TipoPrest' = 'NULL', NULL, '$TipoPrest')
                            ,IIF('$CausaS1' = 'NULL', NULL, '$CausaS1')
                            ,'$CodDisp'
                            ,IIF('$CanForm' = 'NULL', NULL, '$CanForm')
                            ,IIF('$CodFreUso' = 'NULL', NULL, '$CodFreUso')
                            ,IIF('$Cant' = 'NULL', NULL, '$Cant')
                            ,IIF('$CodPerDurTrat' = 'NULL', NULL, '$CodPerDurTrat')
                            ,IIF('$JustNoPBS' = 'NULL', NULL, '$JustNoPBS')
                            ,IIF('$IndRec' = 'NULL', NULL, '$IndRec')
                            ,IIF('$EstJM' = 'NULL', NULL, '$EstJM')
                            ,IIF('$CantTotal' = 'NULL', NULL, '$CantTotal')
                            )
                            END ELSE BEGIN 
                            UPDATE [dbo].[MIPRES_DISPOSITIVOS] WITH (ROWLOCK)
                            SET [NOPRESCRIPCION] = '$NoPrescripcion'
                            ,[CONORDEN] = IIF('$ConOrden' = 'NULL', NULL, '$ConOrden')
                            ,[TIPOPREST] = IIF('$TipoPrest' = 'NULL', NULL, '$TipoPrest')
                            ,[CAUSAS1] = IIF('$CausaS1' = 'NULL', NULL, '$CausaS1')
                            ,[CODDISP] = '$CodDisp'
                            ,[CANFORM] = IIF('$CanForm' = 'NULL', NULL, '$CanForm')
                            ,[CODFREUSO] = IIF('$CodFreUso' = 'NULL', NULL, '$CodFreUso')
                            ,[CANT] = IIF('$Cant' = 'NULL', NULL, '$Cant')
                            ,[CODPERDURTRAT] = IIF('$CodPerDurTrat' = 'NULL', NULL, '$CodPerDurTrat')
                            ,[JUSTNOPBS] = IIF('$JustNoPBS' = 'NULL', NULL, '$JustNoPBS')
                            ,[INDREC] = IIF('$IndRec' = 'NULL', NULL, '$IndRec')
                            ,[ESTJM] = IIF('$EstJM' = 'NULL', NULL, '$EstJM')
                            ,[CANTTOTAL] = IIF('$CantTotal' = 'NULL', NULL, '$CantTotal')
                            WHERE NOPRESCRIPCION = '$NoPrescripcion' AND CONORDEN = '$ConOrden'
                            END";

        return ejecutarConsulta($sql_dispositivos);
    }

    /**
     * Metodo que inserta los medicamentos S y C
     * @param String $NoPrescripcion
     * @param String $ConOrden
     * @param String $TipoMed
     * @param String $TipoPrest
     * @param String $CausaS1
     * @param String $CausaS2
     * @param String $CausaS3
     * @param String $MedPBSUtilizado
     * @param String $RznCausaS31
     * @param String $DescRzn31
     * @param String $RznCausaS32
     * @param String $DescRzn32
     * @param String $CausaS4
     * @param String $MedPBSDescartado
     * @param String $RznCausaS41
     * @param String $DescRzn41
     * @param String $RznCausaS42
     * @param String $DescRzn42
     * @param String $RznCausaS43
     * @param String $DescRzn43
     * @param String $RznCausaS44
     * @param String $DescRzn44
     * @param String $CausaS5
     * @param String $RznCausaS5
     * @param String $CausaS6
     * @param String $DescMedPrinAct
     * @param String $CodFF
     * @param String $CodVA
     * @param String $JustNoPBS
     * @param String $Dosis
     * @param String $DosisUM
     * @param String $NoFAdmon
     * @param String $CodFreAdmon
     * @param String $IndEsp
     * @param String $CanTrat
     * @param String $DurTrat
     * @param String $CantTotalF
     * @param String $UFCantTotal
     * @param String $IndRec
     * @param String $EstJM
     * @return obj
     */
    public function insertar_medicamento($NoPrescripcion, $ConOrden, $TipoMed, $TipoPrest, $CausaS1, $CausaS2, $CausaS3, $MedPBSUtilizado, $RznCausaS31, $DescRzn31,
            $RznCausaS32, $DescRzn32, $CausaS4, $MedPBSDescartado, $RznCausaS41, $DescRzn41, $RznCausaS42, $DescRzn42, $RznCausaS43, $DescRzn43, $RznCausaS44,
            $DescRzn44, $CausaS5, $RznCausaS5, $CausaS6, $DescMedPrinAct, $CodFF, $CodVA, $JustNoPBS, $Dosis, $DosisUM, $NoFAdmon, $CodFreAdmon, $IndEsp,
            $CanTrat, $DurTrat, $CantTotalF, $UFCantTotal, $IndRec, $EstJM) {


        $sql_medicamento = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED
                            IF NOT EXISTS (SELECT NOPRESCRIPCION FROM [MIPRES_MEDICAMENTOS ] WHERE NOPRESCRIPCION = '$NoPrescripcion' AND CONORDEN = '$ConOrden')
                            BEGIN 
                            INSERT INTO [dbo].[MIPRES_MEDICAMENTOS ]
                            ([NOPRESCRIPCION]
                            ,[CONORDEN]
                            ,[TIPOMED]
                            ,[TIPOPREST]
                            ,[CAUSAS1]
                            ,[CAUSAS2]
                            ,[CAUSAS3]
                            ,[MEDPBSUTILIZADO]
                            ,[RZNCAUSAS31]
                            ,[DESCRZN31]
                            ,[RZNCAUSAS32]
                            ,[DESCRZN32]
                            ,[CAUSAS4]
                            ,[MEDPBSDESCARTADO]
                            ,[RZNCAUSAS41]
                            ,[DESCRZN41]
                            ,[RZNCAUSAS42]
                            ,[DESCRZN42]
                            ,[RZNCAUSAS43]
                            ,[DESCRZN43]
                            ,[RZNCAUSAS44]
                            ,[DESCRZN44]
                            ,[CAUSAS5]
                            ,[RZNCAUSAS5]
                            ,[CAUSAS6]
                            ,[DESCMEDPRINACT]
                            ,[CODFF]
                            ,[CODVA]
                            ,[JUSTNOPBS]
                            ,[DOSIS]
                            ,[DOSISUM]
                            ,[NOFADMON]
                            ,[CODFREADMON]
                            ,[INDESP]
                            ,[CANTRAT]
                            ,[DURTRAT]
                            ,[CANTTOTALF]
                            ,[UFCANTTOTAL]
                            ,[INDREC]
                            ,[ESTJM])
                            VALUES
                            ('$NoPrescripcion'
                            ,IIF('$ConOrden' = 'NULL', NULL, '$ConOrden')
                            ,IIF('$TipoMed' = 'NULL', NULL, '$TipoMed')
                            ,IIF('$TipoPrest' = 'NULL', NULL, '$TipoPrest')
                            ,IIF('$CausaS1' = 'NULL', NULL, '$CausaS1')
                            ,IIF('$CausaS2' = 'NULL', NULL, '$CausaS2')
                            ,IIF('$CausaS3' = 'NULL', NULL, '$CausaS3')
                            ,'$MedPBSUtilizado'
                            ,IIF('$RznCausaS31' = 'NULL', NULL, '$RznCausaS31')
                            ,'$DescRzn31'
                            ,IIF('$RznCausaS32' = 'NULL', NULL, '$RznCausaS32')
                            ,'$DescRzn32'
                            ,IIF('$CausaS4' = 'NULL', NULL, '$CausaS4')
                            ,'$MedPBSDescartado'
                            ,IIF('$RznCausaS41' = 'NULL', NULL, '$RznCausaS41')
                            ,'$DescRzn41'
                            ,IIF('$RznCausaS42' = 'NULL', NULL, '$RznCausaS42')
                            ,'$DescRzn42'
                            ,IIF('$RznCausaS43' = 'NULL', NULL, '$RznCausaS43')
                            ,'$DescRzn43'
                            ,IIF('$RznCausaS44' = 'NULL', NULL, '$RznCausaS44')
                            ,'$DescRzn44'
                            ,IIF('$CausaS5' = 'NULL', NULL, '$CausaS5')
                            ,IIF('$RznCausaS5' = 'NULL', NULL, '$RznCausaS5')
                            ,IIF('$CausaS6' = 'NULL', NULL, '$CausaS6')
                            ,'$DescMedPrinAct'
                            ,'$CodFF'
                            ,'$CodVA'
                            ,'$JustNoPBS'
                            ,IIF('$Dosis' = 'NULL', NULL, '$Dosis')
                            ,'$DosisUM'
                            ,IIF('$NoFAdmon' = 'NULL', NULL, '$NoFAdmon')
                            ,IIF('$CodFreAdmon' = 'NULL', NULL, '$CodFreAdmon')
                            ,IIF('$IndEsp' = 'NULL', NULL, '$IndEsp')
                            ,IIF('$CanTrat' = 'NULL', NULL, '$CanTrat')
                            ,IIF('$DurTrat' = 'NULL', NULL, '$DurTrat')
                            ,IIF('$CantTotalF' = 'NULL', NULL, '$CantTotalF')
                            ,'$UFCantTotal'
                            ,'$IndRec'
                            ,IIF('$EstJM' = 'NULL', NULL, '$EstJM')
                            )

                            END ELSE BEGIN 

                            UPDATE [dbo].[MIPRES_MEDICAMENTOS ] WITH (ROWLOCK)
                            SET [NOPRESCRIPCION] = '$NoPrescripcion'
                            ,[CONORDEN] = IIF('$ConOrden' = 'NULL', NULL, '$ConOrden')
                            ,[TIPOMED] = IIF('$TipoMed' = 'NULL', NULL, '$TipoMed')
                            ,[TIPOPREST] = IIF('$TipoPrest' = 'NULL', NULL, '$TipoPrest')
                            ,[CAUSAS1] = IIF('$CausaS1' = 'NULL', NULL, '$CausaS1')
                            ,[CAUSAS2] = IIF('$CausaS2' = 'NULL', NULL, '$CausaS2')
                            ,[CAUSAS3] = IIF('$CausaS3' = 'NULL', NULL, '$CausaS3')
                            ,[MEDPBSUTILIZADO] = '$MedPBSUtilizado'
                            ,[RZNCAUSAS31] = IIF('$RznCausaS31' = 'NULL', NULL, '$RznCausaS31')
                            ,[DESCRZN31] = '$DescRzn31'
                            ,[RZNCAUSAS32] = IIF('$RznCausaS32' = 'NULL', NULL, '$RznCausaS32')
                            ,[DESCRZN32] = '$DescRzn32'
                            ,[CAUSAS4] = IIF('$CausaS4' = 'NULL', NULL, '$CausaS4')
                            ,[MEDPBSDESCARTADO] = '$MedPBSDescartado'
                            ,[RZNCAUSAS41] = IIF('$RznCausaS41' = 'NULL', NULL, '$RznCausaS41')
                            ,[DESCRZN41] = '$DescRzn41'
                            ,[RZNCAUSAS42] = IIF('$RznCausaS42' = 'NULL', NULL, '$RznCausaS42')
                            ,[DESCRZN42] = '$DescRzn42'
                            ,[RZNCAUSAS43] = IIF('$RznCausaS43' = 'NULL', NULL, '$RznCausaS43')
                            ,[DESCRZN43] = '$DescRzn43'
                            ,[RZNCAUSAS44] = IIF('$RznCausaS44' = 'NULL', NULL, '$RznCausaS44')
                            ,[DESCRZN44] = '$DescRzn44'
                            ,[CAUSAS5] = IIF('$CausaS5' = 'NULL', NULL, '$CausaS5')
                            ,[RZNCAUSAS5] = IIF('$RznCausaS5' = 'NULL', NULL, '$RznCausaS5')
                            ,[CAUSAS6] = IIF('$CausaS6' = 'NULL', NULL, '$CausaS6')
                            ,[DESCMEDPRINACT] = '$DescMedPrinAct'
                            ,[CODFF] = '$CodFF'
                            ,[CODVA] = '$CodVA'
                            ,[JUSTNOPBS] = '$JustNoPBS'
                            ,[DOSIS] = IIF('$Dosis' = 'NULL', NULL, '$Dosis')
                            ,[DOSISUM] = '$DosisUM'
                            ,[NOFADMON] = IIF('$NoFAdmon' = 'NULL', NULL, '$NoFAdmon')
                            ,[CODFREADMON] = IIF('$CodFreAdmon' = 'NULL', NULL, '$CodFreAdmon')
                            ,[INDESP] = IIF('$IndEsp' = 'NULL', NULL, '$IndEsp')
                            ,[CANTRAT] = IIF('$CanTrat' = 'NULL', NULL, '$CanTrat')
                            ,[DURTRAT] = IIF('$DurTrat' = 'NULL', NULL, '$DurTrat')
                            ,[CANTTOTALF] = IIF('$CantTotalF' = 'NULL', NULL, '$CantTotalF')
                            ,[UFCANTTOTAL] = '$UFCantTotal'
                            ,[INDREC] = '$IndRec'
                            ,[ESTJM] = IIF('$EstJM' = 'NULL', NULL, '$EstJM')
                            WHERE NOPRESCRIPCION = '$NoPrescripcion' AND CONORDEN = '$ConOrden'
                            END";


        return ejecutarConsulta($sql_medicamento);
    }

    /**
     * Metodo que inserta los principios activos S y C
     * @param String $NoPrescripcion
     * @param String $ConOrden
     * @param String $CodPriAct
     * @param String $ConcCant
     * @param String $UMedConc
     * @param String $CantCont
     * @param String $UMedCantCont
     * @return obj
     */
    public function insertar_prinActivo($NoPrescripcion, $ConOrden, $CodPriAct, $ConcCant, $UMedConc, $CantCont, $UMedCantCont) {

        $sql_principio = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED
                            IF NOT EXISTS (SELECT NOPRESCRIPCION FROM [MIPRES_PRINCIPOACTIVO ] WHERE NOPRESCRIPCION = '$NoPrescripcion' AND CONORDEN = '$ConOrden' AND CodPriAct = '$CodPriAct')
                            BEGIN 

                            INSERT INTO [dbo].[MIPRES_PRINCIPOACTIVO ]
                            ([NOPRESCRIPCION],[CONORDEN],[CODPRIACT],[CONCCANT],[UMEDCONC],[CANTCONT],[UMEDCANTCONT])
                            VALUES
                            ('$NoPrescripcion'
                            ,IIF('$ConOrden' = 'NULL', NULL, '$ConOrden')
                            ,'$CodPriAct'
                            ,'$ConcCant'
                            ,'$UMedConc'
                            ,'$CantCont'
                            ,'$UMedCantCont'
                            )
                            
                            END ELSE BEGIN 
                            
                            UPDATE [dbo].[MIPRES_PRINCIPOACTIVO ] WITH (ROWLOCK)
                            SET [NOPRESCRIPCION] = '$NoPrescripcion'
                            ,[CONORDEN] = IIF('$ConOrden' = 'NULL', NULL, '$ConOrden')
                            ,[CODPRIACT] = '$CodPriAct'
                            ,[CONCCANT] = '$ConcCant'
                            ,[UMEDCONC] = '$UMedConc'
                            ,[CANTCONT] = '$CantCont'
                            ,[UMEDCANTCONT] = '$UMedCantCont'
                            WHERE NOPRESCRIPCION = '$NoPrescripcion' AND CONORDEN = '$ConOrden' AND CodPriAct = '$CodPriAct'

                            END";


        return ejecutarConsulta($sql_principio);
    }

}
