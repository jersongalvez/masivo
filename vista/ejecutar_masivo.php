<?php

////////////////////////////////////////////////////////////////////////////////
/////////////////////////        MASIVO MIPRES         /////////////////////////
/////////////////////////      PIJAOS SALUD EPSI      //////////////////////////
///////////////////     EJECUCION DEL CONSUMO DE SERVICIOS      ////////////////
/////////////////////////  DEPARTAMENTO DE DESARROLLO  /////////////////////////
////////////////////////////////////////////////////////////////////////////////
//Ejecucion del archivo durante 15 minutos
set_time_limit(900);

//Archivo de los controladores
require_once '../controlador/Consumows.php';

//Trae los metodos necesarios para consumir el WS
$masivo = new Consumows();

//Hora Colombia
date_default_timezone_set('America/Bogota');
//Fecha actual
//$fecha = date("Y-m-d");
$fecha = date("2021-09-10");


############################     REGIMENES     ################################
define('SUBSIDIADO', 'S');
define('CONTRIBUTIVO', 'C');

#########################     DIA DE CONSUMO     ###############################
echo PHP_EOL . PHP_EOL . PHP_EOL;
echo 'Consumo de servicios del día: ' . $fecha . PHP_EOL . PHP_EOL;


#########################   INICIO DEL PROCESO   ###############################
echo PHP_EOL . 'Proceso iniciado el: ' . date("Y-m-d H:i:s") . PHP_EOL;
//Guardado del log de inicio del proceso de consumo
$masivo->crear_log($fecha, '>>> Proceso iniciado el: ' . date("Y-m-d H:i:s") . ' <<<', '1');


############################################################################
########################      PRESCRIPCIONES       #########################
############################################################################
#
//Subsidiados
echo '*** Prescripciones subsidiados ***' . PHP_EOL;
$masivo->get_prescripciones(SUBSIDIADO, $fecha);

//Contributivo
echo PHP_EOL . '*** Prescripciones contributivo ***' . PHP_EOL;
$masivo->get_prescripciones(CONTRIBUTIVO, $fecha);


############################################################################
########################     DIRECCIONAMIENTOS     #########################
############################################################################
#
//Subsidiados
echo PHP_EOL . '*** Direccionamientos subsidiados ***' . PHP_EOL;
if ($masivo->comprobar_token(SUBSIDIADO) == 1) {

    $masivo->get_direccionamientos(SUBSIDIADO, $fecha);
}

//Contributivo
echo PHP_EOL . '*** Direccionamientos contributivos ***' . PHP_EOL;
if ($masivo->comprobar_token(CONTRIBUTIVO) == 1) {

    $masivo->get_direccionamientos(CONTRIBUTIVO, $fecha);
}

############################################################################
########################     REPORTE PROVEEDOR     #########################
############################################################################
#
//Subsidiados
echo PHP_EOL . '*** Reporte entrega subsidiado ***' . PHP_EOL;
if ($masivo->comprobar_token(SUBSIDIADO) == 1) {

    $masivo->get_reporteEntrega(SUBSIDIADO, $fecha, '14');
}



############################################################################
########################          TUTELAS          #########################
############################################################################
#
//Subsidiados
//echo PHP_EOL . '*** Tutelas subsidiadas ***' . PHP_EOL;
//$masivo->get_tutelas(SUBSIDIADO, $fecha);
//
//
//
############################################################################
########################         SUMINISTRO        #########################
############################################################################
#
//Subsidiados
echo PHP_EOL . '*** Suministros subsidiado ***' . PHP_EOL;
if ($masivo->comprobar_token(SUBSIDIADO) == 1) {

    $masivo->get_sumistro(SUBSIDIADO, $fecha);
}

//Contributivo
echo PHP_EOL . '*** Suministros contributivos ***' . PHP_EOL;
if ($masivo->comprobar_token(CONTRIBUTIVO) == 1) {

    $masivo->get_sumistro(CONTRIBUTIVO, $fecha);
}

############################################################################
########################    NO DIRECCIONAMIENTO    #########################
############################################################################
#
//Subsidiados
echo PHP_EOL . '*** No direccionamientos subsidiado ***' . PHP_EOL;
if ($masivo->comprobar_token(SUBSIDIADO) == 1) {

    $masivo->get_noDireccionamiento(SUBSIDIADO, $fecha);
}

############################################################################
#######################    NOVEDADES PRESCRIPCION    #######################
############################################################################
#
//Subsidiados
echo PHP_EOL . '*** Novedades prescripción subsidiados ***' . PHP_EOL;
$masivo->get_novedadPrescripcion(SUBSIDIADO, $fecha);

//Contributivo
echo PHP_EOL . '*** Novedades prescripción contributivos ***' . PHP_EOL;
$masivo->get_novedadPrescripcion(CONTRIBUTIVO, $fecha);


############################################################################
#######################     JUNTA PROFESIONALES      #######################
############################################################################
#
//Subsidiados
echo PHP_EOL . '*** Junta profesionales subsidiados ***' . PHP_EOL;
$masivo->get_juntaProfesionales(SUBSIDIADO, $fecha);

//Contributivo
echo PHP_EOL . '*** Junta profesionales contributivos ***' . PHP_EOL;
$masivo->get_juntaProfesionales(CONTRIBUTIVO, $fecha);


############################################################################
#######################        SUMINISTRO V1         #######################
############################################################################
#
//Subsidiados
echo PHP_EOL . '*** Suministro V1 subsidiados ***' . PHP_EOL;
$masivo->get_suministroVer1(SUBSIDIADO, $fecha);


############################################################################
#######################         FACTURACION          #######################
############################################################################
#
//Subsidiados
echo PHP_EOL . '*** Facturacion subsidiados ***' . PHP_EOL;
if ($masivo->comprobar_token(SUBSIDIADO) == 1) {

    $masivo->get_facturacion(SUBSIDIADO, $fecha, '22');
}


############################################################################
#######################      DATOS FACTURADOS        #######################
############################################################################
#
//Subsidiados
echo PHP_EOL . '*** Datos facturados subsidiados ***' . PHP_EOL;
if ($masivo->comprobar_token(SUBSIDIADO) == 1) {

    $masivo->get_datosFacturados(SUBSIDIADO, $fecha);
}

//Contributivo
echo PHP_EOL . '*** Datos facturados contributivos ***' . PHP_EOL;
if ($masivo->comprobar_token(CONTRIBUTIVO) == 1) {

    $masivo->get_datosFacturados(CONTRIBUTIVO, $fecha);
}


############################################################################
######################   RETROACTIVO FACTURACION     #######################
############################################################################
#
//Subsidiados
echo PHP_EOL . '*** Retroactivos Facturacion ***' . PHP_EOL;
$masivo->crear_log($fecha, PHP_EOL . '** Retroactivos Facturacion  **');

if (($rpta = $masivo->get_retroactivoFa())) {

    foreach ($rpta as $salida) {

        if ($masivo->comprobar_token(SUBSIDIADO) == 1) {

            $masivo->get_facturacion(SUBSIDIADO, $fecha, '25', $salida["PRESCRIPCION"]);
        }
        //Espero un segundo para hacer la consulta nuevamente
        sleep(1);
    }
} else {

    echo '- No hay retroactivos para consultar' . PHP_EOL;
    // Guardo el log de terminacion del proceso de consumo
    $masivo->crear_log($fecha, 'No hay retroactivos para consultar');
}


############################################################################
###############   RETROACTIVO REPORTE ENTREGA PROVEEDOR     ################
############################################################################
#
//Subsidiados
echo PHP_EOL . '*** Retroactivos reporte entrega proveedor ***' . PHP_EOL;
$masivo->crear_log($fecha, PHP_EOL . '** Retroactivos reporte entrega proveedor  **');

if (($rpta = $masivo->get_retroactivoRE())) {

    foreach ($rpta as $salida) {

        if ($masivo->comprobar_token(SUBSIDIADO) == 1) {

            $masivo->get_reporteEntrega(SUBSIDIADO, $fecha, '24', $salida["PRESCRIPCION"]);
        }
        //Espero un segundo para hacer la consulta nuevamente
        sleep(1);
    }
} else {

    echo '- No hay retroactivos para consultar' . PHP_EOL;
    // Guardo el log de terminacion del proceso de consumo
    $masivo->crear_log($fecha, 'No hay retroactivos para consultar');
}



#########################   FIN DEL PROCESO   ###############################
echo PHP_EOL . 'Proceso terminado el: ' . date("Y-m-d H:i:s") . PHP_EOL;
// Guardo el log de terminacion del proceso de consumo
$masivo->crear_log($fecha, PHP_EOL . '>>> Proceso terminado el: ' . date("Y-m-d H:i:s") . ' <<<');



