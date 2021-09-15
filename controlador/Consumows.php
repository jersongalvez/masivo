<?php

////////////////////////////////////////////////////////////////////////////////
/////////////////////////        MASIVO MIPRES         /////////////////////////
/////////////////////////      PIJAOS SALUD EPSI      //////////////////////////
///////////////////      CONTROLADOR COMSUMO WEB SERVICE      //////////////////
/////////////////////////  DEPARTAMENTO DE DESARROLLO  /////////////////////////
////////////////////////////////////////////////////////////////////////////////

require '../modelo/Token.php';
require '../modelo/Prescripciones.php';
require '../modelo/Direccionamiento.php';
require '../modelo/Reporteprov.php';
require '../modelo/Suministro.php';
require '../modelo/Tutela.php';
require '../modelo/NoDireccionamiento.php';
require '../modelo/Novedadprescripcion.php';
require '../modelo/Juntaprofesionales.php';
require '../modelo/Suministrov1.php';
require '../modelo/Facturacion.php';
require '../modelo/Datosfacturados.php';

class Consumows {

    private $nit;
    private $token;
    private $prescripcion;
    private $direccionamiento;
    private $reporteProv;
    private $sumistro;
    private $tutela;
    private $noDireccionamiento;
    private $novPrescripcion;
    private $juntaProfesionales;
    private $suministroV1;
    private $facturacion;
    private $datosFacturados;

    //Implementamos nuestro constructor
    public function __construct() {

        $this->nit = '809008362';

        //Instancia a la clase de los modelos
        //Modelo Token
        $this->token = new Token();

        //Modelo prescripcion
        $this->prescripcion = new Prescripciones();

        //Modelo direccionamiento
        $this->direccionamiento = new Direccionamiento();

        //Modelo reporte proveedor
        $this->reporteProv = new Reporteprov();

        //Modelo suministro
        $this->sumistro = new Suministro();

        //Modelo tutela
        $this->tutela = new Tutela();

        //Modelo No Direccionamiento
        $this->noDireccionamiento = new NoDireccionamiento();

        //Modelo novedades prescripciones
        $this->novPrescripcion = new Novedadprescripcion();

        //Modelo junta profesionales
        $this->juntaProfesionales = new Juntaprofesionales();

        //Modelo suministro version uno (1)
        $this->suministroV1 = new Suministrov1();

        //Modelo facturacion
        $this->facturacion = new Facturacion();

        //Modelo datos facturados
        $this->datosFacturados = new Datosfacturados();
    }

    ############################################################################
    ######################## VALIDACION TOKEN TEMPORAL #########################
    ############################################################################

    /**
     * Valida si el token temporal esta habilitado
     * @param String $regimen
     * @return array
     */
    private function validar_token($regimen) {

        //Obtener el token temporal en funcion del regimen
        $token_temporal = sqlsrv_fetch_object($this->token->validar_token($regimen, '19'));

        $url = $token_temporal->DES_URL . $this->nit . '/' . $token_temporal->DES_TEM_TOKEN . '/0';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json; charset=utf-8'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_exec($ch);
        $getinfo = curl_getinfo($ch);

        return $getinfo['http_code'];
    }

    /**
     * Metodo que genera un nuevo token
     * @param String $regimen
     */
    private function generar_token($regimen) {

        $datos = sqlsrv_fetch_object(($regimen === 'S') ? $this->token->obtener_tokenPermanente('S') : $this->token->obtener_tokenPermanente('C'));

        $url = $datos->DES_URL . '' . $datos->NUM_DOCUMENTO . '/' . $datos->TOKENP;
        $salida = $this->consumir_WebService($url);

        if ($salida['http_code'] == 200) {

            //Inserto el nuevo token
            $this->token->guardar_token($regimen, preg_replace('/"/', '', $salida['mensaje']));
            $retorno = 1;
        } else {

            //Retorno negativo
            $retorno = 0;
        }

        return $retorno;
    }

    /**
     * Metodo que comprueba si el token actual es valido, de lo contrario
     * genera uno nuevo
     * @param String $regimen
     */
    public function comprobar_token($regimen) {

        //Controla las iteraciones del ciclo
        $x = 0;
        //Retorno del resultado final de la validacion, si es igual a 1
        //el token se genero de forma correcta
        $retorno = 0;

        //Se valida maximo tres veces que el token temporal este habilitado
        do {

            //Se valida con Swagger que el token este valido, si es verdadero
            //se termina con la iteracion del ciclo.
            //de lo contrario se genera un nuevo token temporal
            if (($validar_tk = $this->validar_token($regimen)) == 200) {

                $x = 3;
                $retorno = 1;
            } else {

                //Si el token de genera de forma correcta termino el ciclo
                //de lo contrario continuo validando
                if (($gen_tk = $this->generar_token($regimen)) == 1) {

                    $x = 3;
                    $retorno = 1;
                } else {

                    $x++;
                    //Espero un segundo para hacer la consulta nuevamente
                    sleep(1);
                }
            }
        } while ($x < 3);


        return $retorno;
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    ############################################################################
    ########################      PRESCRIPCIONES       #########################
    ############################################################################

    /**
     * Metodo que consume las prescripciones S y C 
     * @param String $regimen
     * @param date $fecha
     */
    public function get_prescripciones($regimen, $fecha) {

        //Creo un log informando que se inicio con el consumo de las prescripciones
        Consumows::crear_log($fecha, PHP_EOL . '*** Prescripciones ' . $regimen . ' ***');

        //Obtener el token temporal en funcion del regimen
        $tk_tempres = sqlsrv_fetch_object($this->prescripcion->url_prescripcion($regimen, '2'));

        $url = $tk_tempres->DES_URL . $this->nit . '/' . $fecha . '/' . $tk_tempres->TOKENP;

        $salida = $this->consumir_WebService($url);

        if ($salida['http_code'] == 200) {

            if (($result = (json_decode($salida['mensaje'], true)))) {

                //Consumo prescripciones
                for ($i = 0, $size = count($result); $i < $size; ++$i) {

                    $insert = $this->prescripcion->insertar_prescripcion(
                            $result[$i]['prescripcion']['NoPrescripcion']
                            , $result[$i]['prescripcion']['FPrescripcion']
                            , $result[$i]['prescripcion']['HPrescripcion']
                            , $this->valor(str_replace(",", ".", $result[$i]['prescripcion']['CodHabIPS']))
                            , $result[$i]['prescripcion']['TipoIDIPS']
                            , $this->CompletarCeros($result[$i]['prescripcion']['NroIDIPS'], 15)
                            , $result[$i]['prescripcion']['CodDANEMunIPS']
                            , $result[$i]['prescripcion']['DirSedeIPS']
                            , $result[$i]['prescripcion']['TelSedeIPS']
                            , $result[$i]['prescripcion']['TipoIDProf']
                            , $this->valor(str_replace(",", ".", $result[$i]['prescripcion']['NumIDProf']))
                            , $result[$i]['prescripcion']['PNProfS']
                            , $result[$i]['prescripcion']['SNProfS']
                            , $result[$i]['prescripcion']['PAProfS']
                            , $result[$i]['prescripcion']['SAProfS']
                            , $result[$i]['prescripcion']['RegProfS']
                            , $result[$i]['prescripcion']['TipoIDPaciente']
                            , $result[$i]['prescripcion']['NroIDPaciente']
                            , $result[$i]['prescripcion']['PNPaciente']
                            , $result[$i]['prescripcion']['SNPaciente']
                            , $result[$i]['prescripcion']['PAPaciente']
                            , $result[$i]['prescripcion']['SAPaciente']
                            , $this->valor(str_replace(",", ".", $result[$i]['prescripcion']['CodAmbAte']))
                            , $this->valor(str_replace(",", ".", $result[$i]['prescripcion']['EnfHuerfana']))
                            , $this->valor(str_replace(",", ".", $result[$i]['prescripcion']['CodEnfHuerfana']))
                            , $result[$i]['prescripcion']['CodDxPpal']
                            , $result[$i]['prescripcion']['CodDxRel1']
                            , $result[$i]['prescripcion']['CodDxRel2']
                            , $this->valor(str_replace(",", ".", $result[$i]['prescripcion']['SopNutricional']))
                            , $result[$i]['prescripcion']['CodEPS']
                            , $result[$i]['prescripcion']['TipoIDMadrePaciente']
                            , $result[$i]['prescripcion']['NroIDMadrePaciente']
                            , $this->valor(str_replace(",", ".", $result[$i]['prescripcion']['TipoTransc']))
                            , $result[$i]['prescripcion']['TipoIDDonanteVivo']
                            , $result[$i]['prescripcion']['NroIDDonanteVivo']
                            , $this->valor(str_replace(",", ".", $result[$i]['prescripcion']['EstPres']))
                            , $this->valor($this->prescripcion->IDORDENITEM($result[$i]['prescripcion']['TipoIDPaciente'], $result[$i]['prescripcion']['NroIDPaciente']))
                    );

                    if ($insert) {

                        echo 'Prescripcion: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' grabada con exito.' . PHP_EOL;
                        //Creo un log informando que la prescripcion se inserto con exito.
                        Consumows::crear_log($fecha, 'Prescripcion: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' grabada.');
                    } else {

                        echo 'Prescripcion: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' no se grabo.' . PHP_EOL;
                        //Creo un log informando que el direccionamiento no se pudo insertar.
                        Consumows::crear_log($fecha, 'Prescripcion: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' no se guardo.');
                    }
                }


                ################################################################
                #Consumo procedimientos
                for ($i = 0, $size = count($result); $i < $size; ++$i) {

                    if (sizeof($result[$i]['procedimientos']) <> 0) {

                        echo PHP_EOL . '-- Procedimientos --' . PHP_EOL;
                        //Creo un log informando que se inicio con el consumo de las prescripciones - procedimientos
                        Consumows::crear_log($fecha, PHP_EOL . '-- Prescripciones - Procedimientos --');

                        for ($p = 0, $size_Pro = sizeof($result[$i]['procedimientos']); $p < $size_Pro; ++$p) {

                            $insert_procedimiento = $this->prescripcion->insertar_procedimiento(
                                    $result[$i]['prescripcion']['NoPrescripcion']
                                    , $this->valor(str_replace(",", ".", $result[$i]['procedimientos'][$p]['ConOrden']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['procedimientos'][$p]['TipoPrest']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['procedimientos'][$p]['CausaS11']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['procedimientos'][$p]['CausaS12']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['procedimientos'][$p]['CausaS2']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['procedimientos'][$p]['CausaS3']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['procedimientos'][$p]['CausaS4']))
                                    , $result[$i]['procedimientos'][$p]['ProPBSUtilizado']
                                    , $this->valor(str_replace(",", ".", $result[$i]['procedimientos'][$p]['CausaS5']))
                                    , $result[$i]['procedimientos'][$p]['ProPBSDescartado']
                                    , $this->valor(str_replace(",", ".", $result[$i]['procedimientos'][$p]['RznCausaS51']))
                                    , $result[$i]['procedimientos'][$p]['DescRzn51']
                                    , $this->valor(str_replace(",", ".", $result[$i]['procedimientos'][$p]['RznCausaS52']))
                                    , $result[$i]['procedimientos'][$p]['DescRzn52']
                                    , $this->valor(str_replace(",", ".", $result[$i]['procedimientos'][$p]['CausaS6']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['procedimientos'][$p]['CausaS7']))
                                    , $result[$i]['procedimientos'][$p]['CodCUPS']
                                    , $this->valor(str_replace(",", ".", $result[$i]['procedimientos'][$p]['CanForm']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['procedimientos'][$p]['CodFreUso']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['procedimientos'][$p]['Cant']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['procedimientos'][$p]['CodPerDurTrat']))
                                    , $result[$i]['procedimientos'][$p]['JustNoPBS']
                                    , $result[$i]['procedimientos'][$p]['IndRec']
                                    , $this->valor(str_replace(",", ".", $result[$i]['procedimientos'][$p]['EstJM']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['procedimientos'][$p]['CantTotal']))
                            );

                            if ($insert_procedimiento) {

                                echo 'Prescripcion: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['procedimientos'][$p]['ConOrden'] . ' grabado con exito.' . PHP_EOL;
                                //Creo un log informando que la prescripcion se inserto con exito.
                                Consumows::crear_log($fecha, 'Prescripcion: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['procedimientos'][$p]['ConOrden'] . ' grabada.');
                            } else {

                                echo 'Prescripcion: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['procedimientos'][$p]['ConOrden'] . ' no se grabo.' . PHP_EOL;
                                //Creo un log informando que el direccionamiento no se pudo insertar.
                                Consumows::crear_log($fecha, 'Prescripcion: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['procedimientos'][$p]['ConOrden'] . ' no se guardo.');
                            }
                        }
                    }
                }

                ################################################################
                #Productos nutricionales

                for ($i = 0, $size = count($result); $i < $size; ++$i) {

                    if (sizeof($result[$i]['productosnutricionales']) <> 0) {

                        echo PHP_EOL . '-- Productos Nutricionales --' . PHP_EOL;
                        //Creo un log informando que se inicio con el consumo de las prescripciones - nutricionales
                        Consumows::crear_log($fecha, PHP_EOL . '-- Prescripciones - Nutricionales --');

                        for ($f = 0, $size_Nutr = sizeof($result[$i]['productosnutricionales']); $f < $size_Nutr; ++$f) {

                            $insert_pnutricional = $this->prescripcion->insertar_pronutricional(
                                    $result[$i]['prescripcion']['NoPrescripcion']
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['ConOrden']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['TipoPrest']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['CausaS1']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['CausaS2']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['CausaS3']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['CausaS4']))
                                    , $result[$i]['productosnutricionales'][$f]['ProNutUtilizado']
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['RznCausaS41']))
                                    , $result[$i]['productosnutricionales'][$f]['DescRzn41']
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['RznCausaS42']))
                                    , $result[$i]['productosnutricionales'][$f]['DescRzn42']
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['CausaS5']))
                                    , $result[$i]['productosnutricionales'][$f]['ProNutDescartado']
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['RznCausaS51']))
                                    , $result[$i]['productosnutricionales'][$f]['DescRzn51']
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['RznCausaS52']))
                                    , $result[$i]['productosnutricionales'][$f]['DescRzn52']
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['RznCausaS53']))
                                    , $result[$i]['productosnutricionales'][$f]['DescRzn53']
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['RznCausaS54']))
                                    , $result[$i]['productosnutricionales'][$f]['DescRzn54']
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['TippProNut']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['DescProdNutr']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['CodForma']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['CodViaAdmon']))
                                    , $result[$i]['productosnutricionales'][$f]['JustNoPBS']
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['Dosis']))
                                    , $result[$i]['productosnutricionales'][$f]['DosisUM']
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['NoFAdmon']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['CodFreAdmon']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['CanTrat']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['DurTrat']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['CantTotalF']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['UFCantTotal']))
                                    , $result[$i]['productosnutricionales'][$f]['IndRec']
                                    , $result[$i]['productosnutricionales'][$f]['NoPrescAso']
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['EstJM']))
                                    , $result[$i]['productosnutricionales'][$f]['DXEnfHuer']
                                    , $result[$i]['productosnutricionales'][$f]['DXVIH']
                                    , $result[$i]['productosnutricionales'][$f]['DXCaPal']
                                    , $result[$i]['productosnutricionales'][$f]['DXEnfRCEV']
                                    , $this->valor(str_replace(",", ".", $result[$i]['productosnutricionales'][$f]['IndEsp']))
                            );

                            if ($insert_pnutricional) {

                                echo 'Producto Nutricional: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['productosnutricionales'][$f]['ConOrden'] . ' grabado con exito.' . PHP_EOL;
                                //Creo un log informando que la prescripcion se inserto con exito.
                                Consumows::crear_log($fecha, 'Producto Nutricional: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['productosnutricionales'][$f]['ConOrden'] . ' grabado.');
                            } else {

                                echo 'Producto Nutricional: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['productosnutricionales'][$f]['ConOrden'] . ' no se grabo.' . PHP_EOL;
                                //Creo un log informando que la prescripcion no se pudo insertar.
                                Consumows::crear_log($fecha, 'Producto Nutricional: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['productosnutricionales'][$f]['ConOrden'] . ' no se guardo.');
                            }
                        }
                    }
                }

                ################################################################
                #Servicios Complementarios

                for ($i = 0, $size = count($result); $i < $size; ++$i) {

                    if (sizeof($result[$i]['serviciosComplementarios']) <> 0) {

                        echo PHP_EOL . '-- Servicios Complementarios --' . PHP_EOL;
                        //Creo un log informando que se inicio con el consumo de las prescripciones - Servicios complementarios
                        Consumows::crear_log($fecha, PHP_EOL . '-- Prescripciones - Servicios Complementarios --');

                        for ($p = 0, $size_Comp = sizeof($result[$i]['serviciosComplementarios']); $p < $size_Comp; ++$p) {

                            $insert_scomplementario = $this->prescripcion->insertar_servcomplementario(
                                    $result[$i]['prescripcion']['NoPrescripcion']
                                    , $this->valor(str_replace(",", ".", $result[$i]['serviciosComplementarios'][$p]['ConOrden']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['serviciosComplementarios'][$p]['TipoPrest']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['serviciosComplementarios'][$p]['CausaS1']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['serviciosComplementarios'][$p]['CausaS2']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['serviciosComplementarios'][$p]['CausaS3']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['serviciosComplementarios'][$p]['CausaS4']))
                                    , $result[$i]['serviciosComplementarios'][$p]['DescCausaS4']
                                    , $this->valor(str_replace(",", ".", $result[$i]['serviciosComplementarios'][$p]['CausaS5']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['serviciosComplementarios'][$p]['CodSerComp']))
                                    , $result[$i]['serviciosComplementarios'][$p]['DescSerComp']
                                    , $this->valor(str_replace(",", ".", $result[$i]['serviciosComplementarios'][$p]['CanForm']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['serviciosComplementarios'][$p]['CodFreUso']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['serviciosComplementarios'][$p]['Cant']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['serviciosComplementarios'][$p]['CodPerDurTrat']))
                                    , $result[$i]['serviciosComplementarios'][$p]['JustNoPBS']
                                    , $result[$i]['serviciosComplementarios'][$p]['IndRec']
                                    , $this->valor(str_replace(",", ".", $result[$i]['serviciosComplementarios'][$p]['EstJM']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['serviciosComplementarios'][$p]['CantTotal']))
                            );

                            if ($insert_scomplementario) {

                                echo 'Servicio Complementario: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['serviciosComplementarios'][$p]['ConOrden'] . ' grabado con exito.' . PHP_EOL;
                                //Creo un log informando que la prescripcion se inserto con exito.
                                Consumows::crear_log($fecha, 'Servicio Complementario: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['serviciosComplementarios'][$p]['ConOrden'] . ' grabado.');
                            } else {

                                echo 'Servicio Complementario: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['serviciosComplementarios'][$p]['ConOrden'] . ' no se grabo.' . PHP_EOL;
                                //Creo un log informando que la prescripcion no se pudo insertar.
                                Consumows::crear_log($fecha, 'Servicio Complementario: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['serviciosComplementarios'][$p]['ConOrden'] . ' no se guardo.');
                            }
                        }
                    }
                }

                ################################################################
                #Dispositivos Medicos
                for ($i = 0, $size = count($result); $i < $size; ++$i) {

                    if (sizeof($result[$i]['dispositivos']) <> 0) {

                        echo PHP_EOL . '-- Dispositivos Medicos --' . PHP_EOL;
                        //Creo un log informando que se inicio con el consumo de las prescripciones - Dispositivos Medicos
                        Consumows::crear_log($fecha, PHP_EOL . '-- Prescripciones - Dispositivos Medicos --');

                        for ($p = 0, $size_Dispo = sizeof($result[$i]['dispositivos']); $p < $size_Dispo; ++$p) {

                            $insert_dispositivo = $this->prescripcion->insertar_dispositivo(
                                    $result[$i]['prescripcion']['NoPrescripcion']
                                    , $this->valor(str_replace(",", ".", $result[$i]['dispositivos'][$p]['ConOrden']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['dispositivos'][$p]['TipoPrest']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['dispositivos'][$p]['CausaS1']))
                                    , $result[$i]['dispositivos'][$p]['CodDisp']
                                    , $this->valor(str_replace(",", ".", $result[$i]['dispositivos'][$p]['CanForm']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['dispositivos'][$p]['CodFreUso']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['dispositivos'][$p]['Cant']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['dispositivos'][$p]['CodPerDurTrat']))
                                    , $result[$i]['dispositivos'][$p]['JustNoPBS']
                                    , $result[$i]['dispositivos'][$p]['IndRec']
                                    , $this->valor(str_replace(",", ".", $result[$i]['dispositivos'][$p]['EstJM']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['dispositivos'][$p]['CantTotal']))
                            );

                            if ($insert_dispositivo) {

                                echo 'Dispositivo Medico: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['dispositivos'][$p]['ConOrden'] . ' grabado con exito.' . PHP_EOL;
                                //Creo un log informando que la prescripcion se inserto con exito.
                                Consumows::crear_log($fecha, 'Dispositivo Medico: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['dispositivos'][$p]['ConOrden'] . ' grabado.');
                            } else {

                                echo 'Dispositivo Medico: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['dispositivos'][$p]['ConOrden'] . ' no se grabo.' . PHP_EOL;
                                //Creo un log informando que la prescripcion no se pudo insertar.
                                Consumows::crear_log($fecha, 'Dispositivo Medico: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['dispositivos'][$p]['ConOrden'] . ' no se guardo.');
                            }
                        }
                    }
                }

                ################################################################
                #Medicamentos
                for ($i = 0, $size = count($result); $i < $size; ++$i) {
                    if (sizeof($result[$i]['medicamentos']) <> 0) {

                        echo PHP_EOL . '-- Medicamento --' . PHP_EOL;
                        //Creo un log informando que se inicio con el consumo de las prescripciones - Medicamento
                        Consumows::crear_log($fecha, PHP_EOL . '-- Prescripciones - Medicamento --');

                        for ($p = 0, $size_med = sizeof($result[$i]['medicamentos']); $p < $size_med; ++$p) {

                            $insert_medicamento = $this->prescripcion->insertar_medicamento(
                                    $result[$i]['prescripcion']['NoPrescripcion']
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['ConOrden']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['TipoMed']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['TipoPrest']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['CausaS1']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['CausaS2']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['CausaS3']))
                                    , $result[$i]['medicamentos'][$p]['MedPBSUtilizado']
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['RznCausaS31']))
                                    , $result[$i]['medicamentos'][$p]['DescRzn31']
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['RznCausaS32']))
                                    , $result[$i]['medicamentos'][$p]['DescRzn32']
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['CausaS4']))
                                    , $result[$i]['medicamentos'][$p]['MedPBSDescartado']
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['RznCausaS41']))
                                    , $result[$i]['medicamentos'][$p]['DescRzn41']
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['RznCausaS42']))
                                    , $result[$i]['medicamentos'][$p]['DescRzn42']
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['RznCausaS43']))
                                    , $result[$i]['medicamentos'][$p]['DescRzn43']
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['RznCausaS44']))
                                    , $result[$i]['medicamentos'][$p]['DescRzn44']
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['CausaS5']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['RznCausaS5']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['CausaS6']))
                                    , $result[$i]['medicamentos'][$p]['DescMedPrinAct']
                                    , $result[$i]['medicamentos'][$p]['CodFF']
                                    , $result[$i]['medicamentos'][$p]['CodVA']
                                    , $result[$i]['medicamentos'][$p]['JustNoPBS']
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['Dosis']))
                                    , $result[$i]['medicamentos'][$p]['DosisUM']
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['NoFAdmon']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['CodFreAdmon']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['IndEsp']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['CanTrat']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['DurTrat']))
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['CantTotalF']))
                                    , $result[$i]['medicamentos'][$p]['UFCantTotal']
                                    , $result[$i]['medicamentos'][$p]['IndRec']
                                    , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['EstJM']))
                            );


                            if ($insert_medicamento) {

                                echo 'Medicamento: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['medicamentos'][$p]['ConOrden'] . ' grabado con exito.' . PHP_EOL;
                                //Creo un log informando que la prescripcion se inserto con exito.
                                Consumows::crear_log($fecha, 'Medicamento: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['medicamentos'][$p]['ConOrden'] . ' grabado.');
                            } else {

                                echo 'Medicamento: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['medicamentos'][$p]['ConOrden'] . ' no se grabo.' . PHP_EOL;
                                //Creo un log informando que la prescripcion no se pudo insertar.
                                Consumows::crear_log($fecha, 'Medicamento: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['medicamentos'][$p]['ConOrden'] . ' no se guardo.');
                            }
                        }
                    }
                }

                ################################################################
                #Principio Activo

                for ($i = 0, $size = count($result); $i < $size; ++$i) {

                    if (sizeof($result[$i]['medicamentos']) <> 0) {

                        for ($p = 0, $size_med = sizeof($result[$i]['medicamentos']); $p < $size_med; ++$p) {

                            if (sizeof($result[$i]['medicamentos'][$p]['PrincipiosActivos']) <> 0) {

                                echo PHP_EOL . '-- Principio Activo --' . PHP_EOL;
                                //Creo un log informando que se inicio con el consumo de las prescripciones - Principio Activo
                                Consumows::crear_log($fecha, PHP_EOL . '-- Prescripciones - Principio Activo --');

                                for ($u = 0, $size_Pact = sizeof($result[$i]['medicamentos'][$p]['PrincipiosActivos']); $u < $size_Pact; ++$u) {

                                    $insert_pactivo = $this->prescripcion->insertar_prinActivo(
                                            $result[$i]['prescripcion']['NoPrescripcion']
                                            , $this->valor(str_replace(",", ".", $result[$i]['medicamentos'][$p]['PrincipiosActivos'][$u]['ConOrden']))
                                            , $result[$i]['medicamentos'][$p]['PrincipiosActivos'][$u]['CodPriAct']
                                            , $result[$i]['medicamentos'][$p]['PrincipiosActivos'][$u]['ConcCant']
                                            , $result[$i]['medicamentos'][$p]['PrincipiosActivos'][$u]['UMedConc']
                                            , $result[$i]['medicamentos'][$p]['PrincipiosActivos'][$u]['CantCont']
                                            , $result[$i]['medicamentos'][$p]['PrincipiosActivos'][$u]['UMedCantCont']
                                    );

                                    if ($insert_pactivo) {

                                        echo 'Principio Activo: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['medicamentos'][$p]['PrincipiosActivos'][$u]['ConOrden'] . ' grabado con exito.' . PHP_EOL;
                                        //Creo un log informando que la prescripcion se inserto con exito.
                                        Consumows::crear_log($fecha, 'Principio Activo: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['medicamentos'][$p]['PrincipiosActivos'][$u]['ConOrden'] . ' grabado.');
                                    } else {

                                        echo 'Principio Activo: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['medicamentos'][$p]['PrincipiosActivos'][$u]['ConOrden'] . ' no se grabo.' . PHP_EOL;
                                        //Creo un log informando que la prescripcion no se pudo insertar.
                                        Consumows::crear_log($fecha, 'Principio Activo: ' . $result[$i]['prescripcion']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['medicamentos'][$p]['PrincipiosActivos'][$u]['ConOrden'] . ' no se guardo.');
                                    }
                                }
                            }
                        }
                    }
                }


                ////////////////////////////////////////////////////////////////
                echo '- Se consumieron ' . $size . ' prescripciones' . PHP_EOL;
            } else {

                echo '- No se encontraron servicios para consumir' . PHP_EOL;
                //Creo un log informando que no hay prescripciones para consumir
                Consumows::crear_log($fecha, 'No se encontraron servicios para consumir');
            }
        } else {

            echo '- No se pudo consumir el servicio' . PHP_EOL;
            //Creo un log informando que no hay conexion con el WS
            Consumows::crear_log($fecha, 'No se pudo consumir el servicio - Error: ' . $salida['http_code']);
        }
    }

    /////////////////////////////////////////////////////////////////////////////
    //
    ############################################################################
    ######################## CONSUMO DIRECCIONAMIENTOS #########################
    ############################################################################

    /**
     * Metodo que consume los direccionamientos S y C.
     * @param String $regimen
     * @param date $fecha
     */
    public function get_direccionamientos($regimen, $fecha) {

        //Creo un log informando que se inicio con el consumo de los direccionamientos.
        Consumows::crear_log($fecha, PHP_EOL . '*** Direccionamientos ' . $regimen . ' ***');

        //Obtener el token temporal en funcion del regimen
        $tk_temdir = sqlsrv_fetch_object($this->direccionamiento->url_direccionamiento($regimen, '7'));

        //Armado de la url para consumir los servicios
        $url = $tk_temdir->DES_URL . $this->nit . '/' . $tk_temdir->DES_TEM_TOKEN . '/' . $fecha;

        //Almaceno en la variable salida lo retornado por el WS
        $salida = $this->consumir_WebService($url);

        //Retorno de respuesa exitosa
        if ($salida['http_code'] == 200) {

            //Valido si hay resultados
            if (($result = (json_decode($salida['mensaje'], true)))) {

                //Recorro los resultados obtenidos e inserto en la tabla MIPRES_DIRECCIONAMIENTOS
                for ($i = 0, $size = count($result); $i < $size; ++$i) {

                    $insert = $this->direccionamiento->insertar_direccionamiento(
                            $result[$i]['ID']
                            , $result[$i]['IDDireccionamiento']
                            , $result[$i]['NoPrescripcion']
                            , $result[$i]['TipoTec']
                            , $result[$i]['ConTec']
                            , $result[$i]['TipoIDPaciente']
                            , $result[$i]['NoIDPaciente']
                            , $result[$i]['NoEntrega']
                            , $result[$i]['NoSubEntrega']
                            , $result[$i]['TipoIDProv']
                            , $result[$i]['NoIDProv']
                            , $result[$i]['CodMunEnt']
                            , date("d/m/Y", strtotime(substr($result[$i]['FecMaxEnt'], 0, 10)))
                            , $result[$i]['CantTotAEntregar']
                            , $result[$i]['DirPaciente']
                            , $result[$i]['CodSerTecAEntregar']
                            , $result[$i]['NoIDEPS']
                            , $result[$i]['CodEPS']
                            , date("d/m/Y", strtotime(substr($result[$i]['FecDireccionamiento'], 0, 10)))
                            , $result[$i]['EstDireccionamiento']
                            , date("d/m/Y", strtotime(substr($result[$i]['FecAnulacion'], 0, 10))));

                    if ($insert) {

                        echo 'Direccionamiento: ' . $result[$i]['ID'] . ' grabado con exito.' . PHP_EOL;
                        //Creo un log informando que el direccionamiento se inserto con exito.
                        Consumows::crear_log($fecha, 'Direccionamiento: ' . $result[$i]['ID'] . ' grabado.');
                    } else {

                        echo 'Direccionamiento: ' . $result[$i]['ID'] . ' no se grabo.' . PHP_EOL;
                        //Creo un log informando que el direccionamiento no se pudo insertar.
                        Consumows::crear_log($fecha, 'Direccionamiento: ' . $result[$i]['ID'] . ' no se guardo.');
                    }
                }

                echo '- Se consumieron ' . $size . ' direccionamientos' . PHP_EOL;
            } else {

                echo '- No se encontraron servicios para consumir' . PHP_EOL;
                //Creo un log informando que no hay direccionamientos para consumir
                Consumows::crear_log($fecha, 'No se encontraron servicios para consumir');
            }
        } else {

            echo '- No se pudo consumir el servicio' . PHP_EOL;
            //Creo un log informando que no hay conexion con el WS
            Consumows::crear_log($fecha, 'No se pudo consumir el servicio - Error: ' . $salida['http_code']);
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    ############################################################################
    ######################## CONSUMO REPORTE PROVEEDOR #########################
    ############################################################################

    /**
     * Metodo que consume los reportes de entrega a proveedor S y C.
     * @param String $regimen
     * @param date $fecha
     */
    public function get_reporteEntrega($regimen, $fecha, $id_url, $prescripcion = '') {


        //Obtener el token temporal en funcion del regimen
        //14 obtiene la busqueda por fecha
        if ($id_url == '14') {

            //Creo un log informando que se inicio con el consumo de los reportes de entrega.
            Consumows::crear_log($fecha, PHP_EOL . '*** Reporte entrega ' . $regimen . ' ***');

            $tk_temprov = sqlsrv_fetch_object($this->reporteProv->url_reporteEnt($regimen, '14'));
            $url = $tk_temprov->DES_URL . $this->nit . '/' . $tk_temprov->DES_TEM_TOKEN . '/' . $fecha;

            //El 24 por prescripcion
        } else if ($id_url == '24') {

            //Creo un log informando que se inicio con el consumo de los retroactivos reporte entrega proveedor.
            Consumows::crear_log($fecha, PHP_EOL . '-- Retroactivos reporte entrega proveedor --');

            $tk_temprov = sqlsrv_fetch_object($this->reporteProv->url_reporteEnt($regimen, '24'));
            $url = $tk_temprov->DES_URL . $this->nit . '/' . $tk_temprov->DES_TEM_TOKEN . '/' . $prescripcion;
        }

        //Almaceno en la variable salida lo retornado por el WS
        $salida = $this->consumir_WebService($url);

        //Retorno de respuesa exitosa
        if ($salida['http_code'] == 200) {

            //Valido si hay resultados
            if (($result = (json_decode($salida['mensaje'], true)))) {

                //Recorro los resultados obtenidos e inserto en la tabla MIPRES_ENTREGA_PROVEEDOR
                for ($i = 0, $size = count($result); $i < $size; ++$i) {

                    $insert = $this->reporteProv->insertar_reporteEntrega(
                            $result[$i]['ID']
                            , $result[$i]['IDReporteEntrega']
                            , $result[$i]['NoPrescripcion']
                            , $result[$i]['TipoTec']
                            , $result[$i]['ConTec']
                            , $result[$i]['TipoIDPaciente']
                            , $result[$i]['NoIDPaciente']
                            , $result[$i]['NoEntrega']
                            , $result[$i]['EstadoEntrega']
                            , $result[$i]['CausaNoEntrega']
                            , $result[$i]['ValorEntregado']
                            , $result[$i]['CodTecEntregado']
                            , $result[$i]['CantTotEntregada']
                            , $result[$i]['NoLote']
                            , date("d/m/Y", strtotime(substr($result[$i]['FecEntrega'], 0, 10)))
                            , date("d/m/Y", strtotime(substr($result[$i]['FecRepEntrega'], 0, 10)))
                            , $result[$i]['EstRepEntrega']
                            , date("d/m/Y", strtotime(substr($result[$i]['FecAnulacion'], 0, 10))));

                    if ($insert) {

                        echo 'Reporte de entrega: ' . $result[$i]['ID'] . ' - ' . $result[$i]['IDReporteEntrega'] . ' grabado con exito.' . PHP_EOL;
                        //Creo un log informando que el reporte se inserto con exito.
                        Consumows::crear_log($fecha, 'Reporte de entrega: ' . $result[$i]['ID'] . ' - ' . $result[$i]['IDReporteEntrega'] . ' grabado.');
                    } else {

                        echo 'Reporte de entrega: ' . $result[$i]['ID'] . ' - ' . $result[$i]['IDReporteEntrega'] . ' no se grabo.' . PHP_EOL;
                        //Creo un log informando que el reporte no se pudo insertar.
                        Consumows::crear_log($fecha, 'Reporte de entrega: ' . $result[$i]['ID'] . ' - ' . $result[$i]['IDReporteEntrega'] . ' no se guardo.');
                    }
                }

                echo '- Se consumieron ' . $size . ' reportes de entrega' . PHP_EOL;
            } else {

                echo '- No se encontraron servicios para consumir' . PHP_EOL;
                //Creo un log informando que no hay reportes para consumir
                Consumows::crear_log($fecha, 'No se encontraron servicios para consumir');
            }
        } else {

            echo '- No se pudo consumir el servicio' . PHP_EOL;
            //Creo un log informando que no hay conexion con el WS
            Consumows::crear_log($fecha, 'No se pudo consumir el servicio - Error: ' . $salida['http_code']);
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    ############################################################################
    ########################    CONSUMO SUMINISTROS    #########################
    ############################################################################

    /**
     * Metodo que consume los suministros S y C.
     * @param String $regimen
     * @param date $fecha
     */
    public function get_sumistro($regimen, $fecha) {

        //Creo un log informando que se inicio con el consumo de los reportes de entrega.
        Consumows::crear_log($fecha, PHP_EOL . '*** Suministro ' . $regimen . ' ***');

        //Obtener el token temporal en funcion del regimen
        $tk_temsum = sqlsrv_fetch_object($this->sumistro->url_suministro($regimen, '20'));

        //Creo la url de consulta
        $url = $tk_temsum->DES_URL . $this->nit . '/' . $tk_temsum->DES_TEM_TOKEN . '/' . $fecha;

        //Asignar el consumo de la consulta al WS
        $salida = $this->consumir_WebService($url);

        //Validacion retorno respuesta exitosa
        if ($salida['http_code'] == 200) {

            //Valido si hay datos para insertar
            if (($result = (json_decode($salida['mensaje'], true)))) {

                //Insercion de datos en la tabla MIPRES_SUMINISTRO
                for ($i = 0, $size = count($result); $i < $size; ++$i) {

                    $insert = $this->sumistro->insertar_suministro(
                            $result[$i]['ID']
                            , $result[$i]['IDSuministro']
                            , $result[$i]['NoPrescripcion']
                            , $result[$i]['TipoTec']
                            , $result[$i]['ConTec']
                            , $result[$i]['TipoIDPaciente']
                            , $result[$i]['NoIDPaciente']
                            , $result[$i]['NoEntrega']
                            , $result[$i]['UltEntrega']
                            , $result[$i]['EntregaCompleta']
                            , $result[$i]['CausaNoEntrega']
                            , $result[$i]['NoPrescripcionAsociada']
                            , $result[$i]['ConTecAsociada']
                            , $result[$i]['CantTotEntregada']
                            , $result[$i]['NoLote']
                            , $result[$i]['ValorEntregado']
                            , date("d/m/Y", strtotime(substr($result[$i]['FecSuministro'], 0, 10)))
                            , $result[$i]['EstSuministro']
                            , date("d/m/Y", strtotime(substr($result[$i]['FecAnulacion'], 0, 10))));

                    if ($insert) {

                        echo 'Suministro: ' . $result[$i]['ID'] . ' grabado con exito.' . PHP_EOL;
                        //Creo un log informando que el suministro se inserto con exito.
                        Consumows::crear_log($fecha, 'Suministro: ' . $result[$i]['ID'] . ' grabado.');
                    } else {

                        echo 'Suministro: ' . $result[$i]['ID'] . ' no se grabo.' . PHP_EOL;
                        //Creo un log informando que el reporte no se pudo insertar.
                        Consumows::crear_log($fecha, 'Suministro: ' . $result[$i]['ID'] . ' no se guardo.');
                    }
                }

                echo '- Se consumieron ' . $size . ' suministros' . PHP_EOL;
            } else {

                echo '- No se encontraron servicios para consumir' . PHP_EOL;
                //Creo un log informando que no hay suministros para consumir
                Consumows::crear_log($fecha, 'No se encontraron servicios para consumir');
            }
        } else {

            echo '- No se pudo consumir el servicio' . PHP_EOL;
            //Creo un log informando que no hay conexion con el WS
            Consumows::crear_log($fecha, 'No se pudo consumir el servicio - Error: ' . $salida['http_code']);
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    ############################################################################
    ########################          TUTELAS          #########################
    ############################################################################

    /**
     * Metodo que consume las tutelas S y C 
     * @param String $regimen
     * @param date $fecha
     */
    public function get_tutelas($regimen, $fecha) {

        //Obtener el token temporal en funcion del regimen
        $tk_temtut = sqlsrv_fetch_object($this->tutela->url_tutela($regimen, '12'));

        $url = $tk_temtut->DES_URL . $this->nit . '/' . $fecha . '/' . $tk_temtut->TOKENP;

        $salida = $this->consumir_WebService($url);

        if ($salida['http_code'] == 200) {

            if (($result = (json_decode($salida['mensaje'], true)))) {

                //Consumo prescripciones
                for ($i = 0, $size = count($result); $i < $size; ++$i) {
                    
                }
            } else {

                echo '- No se encontraron servicios para consumir' . PHP_EOL;
            }
        } else {

            echo '- No se pudo consumir el servicio' . PHP_EOL;
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    ############################################################################
    ####################### CONSUMO NO DIRECCIONAMIENTOS #######################
    ############################################################################

    /**
     * Metodo que consume los no direccionamientos S
     * @param String $regimen
     * @param date $fecha
     */
    public function get_noDireccionamiento($regimen, $fecha) {

        //Creo un log informando que se inicio con el consumo de los reportes de entrega.
        Consumows::crear_log($fecha, PHP_EOL . '*** No direccionamientos ' . $regimen . ' ***');

        //Obtener el token temporal en funcion del regimen
        $tk_temndir = sqlsrv_fetch_object($this->noDireccionamiento->url_noDireccionamiento($regimen, '8'));

        $url = $tk_temndir->DES_URL . $this->nit . '/' . $tk_temndir->DES_TEM_TOKEN . '/' . $fecha;

        $salida = $this->consumir_WebService($url);

        if ($salida['http_code'] == 200) {

            if (($result = (json_decode($salida['mensaje'], true)))) {

                for ($i = 0, $size = count($result); $i < $size; ++$i) {

                    $insert = $this->noDireccionamiento->insertar_noDireccionamiento(
                            $result[$i]['ID']
                            , $result[$i]['IDNODireccionamiento']
                            , $result[$i]['NoPrescripcion']
                            , $result[$i]['TipoTec']
                            , $result[$i]['ConTec']
                            , $result[$i]['TipoIDPaciente']
                            , $result[$i]['NoIDPaciente']
                            , $result[$i]['NoPrescripcionAsociada']
                            , $result[$i]['ConTecAsociada']
                            , $result[$i]['CausaNoEntrega']
                            , date("d/m/Y", strtotime(substr($result[$i]['FecNODireccionamiento'], 0, 10)))
                            , $result[$i]['EstNODireccionamiento']
                            , date("d/m/Y", strtotime(substr($result[$i]['FecAnulacion'], 0, 10))));

                    if ($insert) {

                        echo 'No direccionamiento: ' . $result[$i]['ID'] . ' grabado con exito.' . PHP_EOL;
                        //Creo un log informando que el no direccionamiento se inserto con exito.
                        Consumows::crear_log($fecha, 'No direccionamiento: ' . $result[$i]['ID'] . ' grabado.');
                    } else {

                        echo 'No direccionamiento: ' . $result[$i]['ID'] . ' no se grabo.' . PHP_EOL;
                        //Creo un log informando que el no direccionamiento no se pudo insertar.
                        Consumows::crear_log($fecha, 'No direccionamiento: ' . $result[$i]['ID'] . ' no se guardo.');
                    }
                }

                echo '- Se consumieron ' . $size . ' no direccionamientos' . PHP_EOL;
            } else {

                echo '- No se encontraron servicios para consumir' . PHP_EOL;
                //Creo un log informando que no hay no direccionamientos para consumir
                Consumows::crear_log($fecha, 'No se encontraron servicios para consumir');
            }
        } else {

            echo '- No se pudo consumir el servicio' . PHP_EOL;
            //Creo un log informando que no hay conexion con el WS
            Consumows::crear_log($fecha, 'No se pudo consumir el servicio - Error: ' . $salida['http_code']);
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    ############################################################################
    #######################    NOVEDADES PRESCRIPCION    #######################
    ############################################################################

    /**
     * Metodo que consume las novedades de las prescripciones S y C 
     * @param String $regimen
     * @param date $fecha
     */
    public function get_novedadPrescripcion($regimen, $fecha) {

        //Creo un log informando que se inicio con el consumo de los reportes de entrega.
        Consumows::crear_log($fecha, PHP_EOL . '*** Novedades prescripcion ' . $regimen . ' ***');

        //Almaceno las prescripciones 1: Modificación o 2: Anulación
        //para ser almacenadas en la tabla MIPRES_PRESCRIPCION
        $sin_transcripcion = array();

        //Obtener el token temporal en funcion del regimen
        $tk_tempre = sqlsrv_fetch_object($this->novPrescripcion->url_novPrescripcion($regimen, '13'));

        $url = $tk_tempre->DES_URL . $this->nit . '/' . $fecha . '/' . $tk_tempre->TOKENP;

        $salida = $this->consumir_WebService($url);

        if ($salida['http_code'] == 200) {

            if (($result = (json_decode($salida['mensaje'], true)))) {

                //Inserto las novedades
                for ($i = 0, $size = count($result); $i < $size; ++$i) {

                    //Almaceno las prescripviones 1: Modificación o 2: Anulación
                    //para ser almacenadas en la tabla MIPRES_PRESCRIPCION
                    if ($result[$i]['prescripcion_novedades']['TipoNov'] != 3) {

                        $sin_transcripcion[] = array(
                            "TipoNov" => $result[$i]['prescripcion_novedades']['TipoNov'],
                            "NoPrescripcion" => $result[$i]['prescripcion_novedades']['NoPrescripcion']
                        );
                    }

                    $insert = $this->novPrescripcion->insertar_novPrescripcion(
                            $result[$i]['prescripcion_novedades']['TipoNov']
                            , $result[$i]['prescripcion_novedades']['NoPrescripcion']
                            , $result[$i]['prescripcion_novedades']['NoPrescripcionF']
                            , $result[$i]['prescripcion_novedades']['FNov']
                    );

                    if ($insert) {

                        echo 'Novedad: ' . $result[$i]['prescripcion_novedades']['NoPrescripcion'] . ' grabada con exito.' . PHP_EOL;
                        //Creo un log informando que la novedad se inserto con exito.
                        Consumows::crear_log($fecha, 'Novedad: ' . $result[$i]['prescripcion_novedades']['NoPrescripcion'] . ' grabado.');
                    } else {

                        echo 'Novedad: ' . $result[$i]['prescripcion_novedades']['NoPrescripcion'] . ' no se grabo.' . PHP_EOL;
                        //Creo un log informando que la novedad no se pudo insertar.
                        Consumows::crear_log($fecha, 'Novedad: ' . $result[$i]['prescripcion_novedades']['NoPrescripcion'] . ' no se guardo.');
                    }
                }


                //Actualizo en caso de que el tipo de novedad sea igual a 1: Modificación o 2: Anulación
                if ($sin_transcripcion) {

                    for ($i = 0, $tam = count($sin_transcripcion); $i < $tam; ++$i) {

                        $update = $this->novPrescripcion->actualizar_prescripcion($sin_transcripcion[$i]['TipoNov'], $sin_transcripcion[$i]['NoPrescripcion']);

                        if ($update) {

                            echo 'Prescripción: ' . $sin_transcripcion[$i]['NoPrescripcion'] . ' actualizada con exito.' . PHP_EOL;
                            //Creo un log informando que la novedad se inserto con exito.
                            Consumows::crear_log($fecha, 'Novedad: ' . $sin_transcripcion[$i]['NoPrescripcion'] . ' actualizada.');
                        } else {

                            echo 'Prescripción: ' . $sin_transcripcion[$i]['NoPrescripcion'] . ' no se actualizo.' . PHP_EOL;
                            //Creo un log informando que la novedad no se pudo insertar.
                            Consumows::crear_log($fecha, 'Prescripción: ' . $sin_transcripcion[$i]['NoPrescripcion'] . '  no se actualizo.');
                        }
                    }
                }

                echo '- Se consumieron ' . $size . ' novedades de prescripciones' . PHP_EOL;
            } else {

                echo '- No se encontraron servicios para consumir' . PHP_EOL;
                //Creo un log informando que no hay novedades para consumir
                Consumows::crear_log($fecha, 'No se encontraron servicios para consumir');
            }
        } else {

            echo '- No se pudo consumir el servicio' . PHP_EOL;
            //Creo un log informando que no hay conexion con el WS
            Consumows::crear_log($fecha, 'No se pudo consumir el servicio - Error: ' . $salida['http_code']);
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    ############################################################################
    #######################     JUNTA PROFESIONALES      #######################
    ############################################################################

    /**
     * Metodo que consume las juntas profesionales S y C 
     * @param String $regimen
     * @param date $fecha
     */
    public function get_juntaProfesionales($regimen, $fecha) {

        //Creo un log informando que se inicio con el consumo de los reportes de entrega.
        Consumows::crear_log($fecha, PHP_EOL . '*** Junta profesionales ' . $regimen . ' ***');

        //Obtener el token temporal en funcion del regimen
        $tk_temjpro = sqlsrv_fetch_object($this->juntaProfesionales->url_Juntaprofesional($regimen, '11'));

        $url = $tk_temjpro->DES_URL . $this->nit . '/' . $tk_temjpro->TOKENP . '/' . $fecha;

        $salida = $this->consumir_WebService($url);

        if ($salida['http_code'] == 200) {

            if (($result = (json_decode($salida['mensaje'], true)))) {

                for ($i = 0, $size = count($result); $i < $size; ++$i) {

                    $insert = $this->juntaProfesionales->insertar_Juntaprofesional(
                            $result[$i]['junta_profesional']['NoPrescripcion']
                            , date("d/m/Y", strtotime(substr($result[$i]['junta_profesional']['FPrescripcion'], 0, 10)))
                            , $result[$i]['junta_profesional']['TipoTecnologia']
                            , $this->valor(str_replace(", ", " . ", $result[$i]['junta_profesional']['Consecutivo']))
                            , $this->valor(str_replace(", ", " . ", $result[$i]['junta_profesional']['EstJM']))
                            , $result[$i]['junta_profesional']['CodEntProc']
                            , $result[$i]['junta_profesional']['Observaciones']
                            , $result[$i]['junta_profesional']['JustificacionTecnica']
                            , $this->valor(str_replace(",", ".", $result[$i]['junta_profesional']['Modalidad']))
                            , $result[$i]['junta_profesional']['NoActa']
                            , date("d/m/Y", strtotime(substr($result[$i]['junta_profesional']['FechaActa'], 0, 10)))
                            , date("d/m/Y", strtotime(substr($result[$i]['junta_profesional']['FProceso'], 0, 10)))
                            , $result[$i]['junta_profesional']['TipoIDPaciente']
                            , $result[$i]['junta_profesional']['NroIDPaciente']
                            , $result[$i]['junta_profesional']['CodEntJM']
                    );

                    if ($insert) {

                        echo 'Junta profesional: ' . $result[$i]['junta_profesional']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['junta_profesional']['Consecutivo'] . ' grabada con exito.' . PHP_EOL;
                        //Creo un log informando que la junta de profesionales se inserto con exito.
                        Consumows::crear_log($fecha, 'Junta profesional: ' . $result[$i]['junta_profesional']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['junta_profesional']['Consecutivo'] . ' grabada.');
                    } else {

                        echo 'Junta profesional: ' . $result[$i]['junta_profesional']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['junta_profesional']['Consecutivo'] . ' no se grabo.' . PHP_EOL;
                        //Creo un log informando que la novedad no se pudo insertar.
                        Consumows::crear_log($fecha, 'Junta profesional: ' . $result[$i]['junta_profesional']['NoPrescripcion'] . ' - Consecutivo: ' . $result[$i]['junta_profesional']['Consecutivo'] . ' no se guardo.');
                    }
                }

                echo '- Se consumieron ' . $size . ' juntas de profesionales' . PHP_EOL;
            } else {

                echo '- No se encontraron servicios para consumir' . PHP_EOL;
                //Creo un log informando que no hay juntas de profesionales para consumir
                Consumows::crear_log($fecha, 'No se encontraron servicios para consumir');
            }
        } else {

            echo '- No se pudo consumir el servicio' . PHP_EOL;
            //Creo un log informando que no hay conexion con el WS
            Consumows::crear_log($fecha, 'No se pudo consumir el servicio - Error: ' . $salida['http_code']);
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    ############################################################################
    #######################     SUMINISTRO VERSION 1     #######################
    ############################################################################

    /**
     * Metodo que consume los suministros version uno (1) S
     * @param String $regimen
     * @param date $fecha
     */
    public function get_suministroVer1($regimen, $fecha) {

        //Creo un log informando que se inicio con el consumo de los suministros V1.
        Consumows::crear_log($fecha, PHP_EOL . '*** Suministro V1 ' . $regimen . ' ***');

        //Obtener el token temporal en funcion del regimen
        $tk_temsu1 = sqlsrv_fetch_object($this->suministroV1->url_suministroV1($regimen, '21'));

        $url = $tk_temsu1->DES_URL . $this->nit . '/' . $tk_temsu1->TOKENP . '/' . $fecha;

        $salida = $this->consumir_WebService($url);


        if ($salida['http_code'] == 200) {

            if (($result = (json_decode($salida['mensaje'], true)))) {

                for ($i = 0, $size = count($result); $i < $size; ++$i) {

                    $insert = $this->suministroV1->insertar_suministroV1(
                            $result[$i]['ID']
                            , $result[$i]['NoPrescripcion']
                            , $result[$i]['TipoTecnologia']
                            , $result[$i]['ConOrden']
                            , $result[$i]['TipoIDEntidad']
                            , $result[$i]['NoIdPrestSumServ']
                            , $result[$i]['CodHabIPS']
                            , $result[$i]['FSum']
                            , $result[$i]['TipoIDPaciente']
                            , $result[$i]['NroIDPaciente']
                            , $result[$i]['EntregaMes']
                            , $result[$i]['UltEntrega']
                            , $result[$i]['NoEntParcial']
                            , $result[$i]['EntregaCompleta']
                            , $result[$i]['NoPrescripcionAsociada']
                            , $result[$i]['ConOrdenAsociada']
                            , $result[$i]['CausaNoEntrega']
                            , $result[$i]['CodTecnEntregado']
                            , $result[$i]['CantidadTotalEntregada']
                            , $result[$i]['ValorEntregado']
                            , date("d/m/Y", strtotime(substr($result[$i]['FReporte'], 0, 10)))
                            , $result[$i]['NoLote']
                            , $result[$i]['EstSuministro']
                            , date("d/m/Y", strtotime(substr($result[$i]['FecAnulacion'], 0, 10)))
                    );

                    if ($insert) {

                        echo 'Suministro V1: ' . $result[$i]['ID'] . ' grabado con exito.' . PHP_EOL; //Creo un log informando que la junta de profesionales se inserto con exito.
                        Consumows::crear_log($fecha, 'Suministro V1: ' . $result[$i]['ID'] . ' grabada.');
                    } else {

                        echo 'Suministro V1: ' . $result[$i]['ID'] . ' no se grabo.' . PHP_EOL;
                        //Creo un log informando que el suministro V1 no se pudo insertar.
                        Consumows::crear_log($fecha, 'Suministro V1: ' . $result[$i]['ID'] . ' no se guardo.');
                    }
                }

                echo '- Se consumieron ' . $size . ' suministros V1' . PHP_EOL;
            } else {

                echo '- No se encontraron servicios para consumir' . PHP_EOL;
                //Creo un log informando que no hay suministros V1 para consumir
                Consumows::crear_log($fecha, 'No se encontraron servicios para consumir');
            }
        } else {

            echo '- No se pudo consumir el servicio' . PHP_EOL;
            //Creo un log informando que no hay conexion con el WS
            Consumows::crear_log($fecha, 'No se pudo consumir el servicio - Error: ' . $salida['http_code']);
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    ############################################################################
    ############################     FACTURACION     ###########################
    ############################################################################

    /**
     * Metodo que consume la facturacion S.
     * @param String $regimen
     * @param date $fecha
     */
    public function get_facturacion($regimen, $fecha, $id_url, $prescripcion = '') {


        //Obtener el token temporal en funcion del regimen
        //14 obtiene la busqueda por fecha
        if ($id_url == '22') {

            //Creo un log informando que se inicio con el consumo de la facturacion.
            Consumows::crear_log($fecha, PHP_EOL . '*** Facturacion ' . $regimen . ' ***');

            //Obtener el token temporal en funcion del regimen
            $tk_temfac = sqlsrv_fetch_object($this->facturacion->url_facturacion($regimen, '22'));
            $url = $tk_temfac->DES_URL . $this->nit . '/' . $tk_temfac->DES_TEM_TOKEN . '/' . $fecha;


            //El 25 por prescripcion
        } else if ($id_url == '25') {

            //Creo un log informando que se inicio con el consumo de los retroactivos reporte entrega proveedor.
            Consumows::crear_log($fecha, PHP_EOL . '-- Retroactivos Facturacion --');

            $tk_temfac = sqlsrv_fetch_object($this->facturacion->url_facturacion($regimen, '25'));
            $url = $tk_temfac->DES_URL . $this->nit . '/' . $tk_temfac->DES_TEM_TOKEN . '/' . $prescripcion;
        }


        $salida = $this->consumir_WebService($url);

        if ($salida['http_code'] == 200) {

            if (($result = (json_decode($salida['mensaje'], true)))) {

                for ($i = 0, $size = count($result); $i < $size; ++$i) {

                    $insert = $this->facturacion->insertar_facturacion(
                            $result[$i]['ID']
                            , $result[$i]['IDFacturacion']
                            , $result[$i]['NoPrescripcion']
                            , $result[$i]['TipoTec']
                            , $result[$i]['ConTec']
                            , $result[$i]['TipoIDPaciente']
                            , $result[$i]['NoIDPaciente']
                            , $result[$i]['NoEntrega']
                            , $result[$i]['NoSubEntrega']
                            , $result[$i]['NoFactura']
                            , $result[$i]['NoIDEPS']
                            , $result[$i]['CodEPS']
                            , $result[$i]['CodSerTecAEntregado']
                            , $result[$i]['CantUnMinDis']
                            , $result[$i]['ValorUnitFacturado']
                            , $result[$i]['ValorTotFacturado']
                            , $result[$i]['CuotaModer']
                            , $result[$i]['Copago']
                            , date("d/m/Y", strtotime(substr($result[$i]['FecFacturacion'], 0, 10)))
                            , $result[$i]['EstFacturacion']
                            , date("d/m/Y", strtotime(substr($result[$i]['FecAnulacion'], 0, 10)))
                    );

                    if ($insert) {

                        echo 'Facturacion: ' . $result[$i]['ID'] . ' grabada con exito.' . PHP_EOL;
                        Consumows::crear_log($fecha, 'Facturacion: ' . $result[$i]['ID'] . ' grabada.');
                    } else {

                        echo 'Facturacion: ' . $result[$i]['ID'] . ' no se grabo.' . PHP_EOL;
                        //Creo un log informando que la facturacion no se pudo insertar.
                        Consumows::crear_log($fecha, 'Facturacion: ' . $result[$i]['ID'] . ' no se guardo.');
                    }
                }

                echo '- Se consumieron ' . $size . ' facturas' . PHP_EOL;
            } else {

                echo '- No se encontraron servicios para consumir' . PHP_EOL;
                //Creo un log informando que no facturas para consumir
                Consumows::crear_log($fecha, 'No se encontraron servicios para consumir');
            }
        } else {

            echo '- No se pudo consumir el servicio' . PHP_EOL;
            //Creo un log informando que no hay conexion con el WS
            Consumows::crear_log($fecha, 'No se pudo consumir el servicio - Error: ' . $salida['http_code']);
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    ############################################################################
    #########################     DATOS FACTURADOS   ###########################
    ############################################################################

    /**
     * Metodo que consume los datos facturados S y C
     * @param String $regimen
     * @param date $fecha
     */
    public function get_datosFacturados($regimen, $fecha) {

        //Creo un log informando que se inicio con el consumo de los datos facturados
        Consumows::crear_log($fecha, PHP_EOL . '*** Datos facturados ' . $regimen . ' ***');

        //Obtener el token temporal en funcion del regimen
        $tk_temdfac = sqlsrv_fetch_object($this->datosFacturados->url_datosfacturados($regimen, '23'));

        $url = $tk_temdfac->DES_URL . $this->nit . '/' . $tk_temdfac->DES_TEM_TOKEN . '/' . $fecha;

        $salida = $this->consumir_WebService($url);

        if ($salida['http_code'] == 200) {

            if (($result = (json_decode($salida['mensaje'], true)))) {

                for ($i = 0, $size = count($result); $i < $size; ++$i) {

                    $insert = $this->datosFacturados->insertar_datoFacturado(
                            $result[$i]['ID']
                            , $result[$i]['IDDatosFacturado']
                            , $result[$i]['NoPrescripcion']
                            , $result[$i]['TipoTec']
                            , $result[$i]['ConTec']
                            , $result[$i]['TipoIDPaciente']
                            , $result[$i]['NoIDPaciente']
                            , $result[$i]['NoEntrega']
                            , $result[$i]['CompAdm']
                            , $result[$i]['CodCompAdm']
                            , $result[$i]['CodHom']
                            , $result[$i]['UniCompAdm']
                            , $result[$i]['UniDispHom']
                            , $result[$i]['ValUnMiCon']
                            , $result[$i]['CantTotEnt']
                            , $result[$i]['ValTotCompAdm']
                            , $result[$i]['ValTotHom']
                            , date("d/m/Y", strtotime(substr($result[$i]['FecDatosFacturado'], 0, 10)))
                            , $result[$i]['EstDatosFacturado']
                            , date("d/m/Y", strtotime(substr($result[$i]['FecAnulacion'], 0, 10)))
                    );

                    if ($insert) {

                        echo 'Dato facturado: ' . $result[$i]['ID'] . ' grabado con exito.' . PHP_EOL;
                        Consumows::crear_log($fecha, 'Dato facturado: ' . $result[$i]['ID'] . ' grabado.');
                    } else {

                        echo 'Dato facturado: ' . $result[$i]['ID'] . ' no se grabo.' . PHP_EOL;
                        //Creo un log informando que la facturacion no se pudo insertar.
                        Consumows::crear_log($fecha, 'Dato facturado: ' . $result[$i]['ID'] . ' no se guardo.');
                    }
                }

                echo '- Se consumieron ' . $size . ' facturas' . PHP_EOL;
            } else {

                echo '- No se encontraron servicios para consumir' . PHP_EOL;
                //Creo un log informando que no se encontraron datos facturados para consumir
                Consumows::crear_log($fecha, 'No se encontraron servicios para consumir');
            }
        } else {

            echo '- No se pudo consumir el servicio' . PHP_EOL;
            //Creo un log informando que no hay conexion con el WS
            Consumows::crear_log($fecha, 'No se pudo consumir el servicio - Error: ' . $salida['http_code']);
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    ############################################################################
    ######################   RETROACTIVO FACTURACION     #######################
    ############################################################################

    /**
     * Metodo que obtiene la facturacion con retroactivos
     * @return array
     */
    public function get_retroactivoFa() {

        $rspta = $this->facturacion->get_retroactivoFa();

        $data = Array();

        while ($reg = sqlsrv_fetch_object($rspta)) {

            $data[] = array(
                "PRESCRIPCION" => $reg->NOPRESCRIPCION
            );
        }

        return $data;
    }

    /////////////////////////////////////////////////////////////////////////////
    //
    ############################################################################
    ###############   RETROACTIVO REPORTE ENTREGA PROVEEDOR     ################
    ############################################################################

    /**
     * Metodo que obtiene los reportes de entrega con retroactivos
     * @return array
     */
    public function get_retroactivoRE() {

        $rspta = $this->reporteProv->get_retroactivo();

        $data = Array();

        while ($reg = sqlsrv_fetch_object($rspta)) {

            $data[] = array(
                "PRESCRIPCION" => $reg->NOPRESCRIPCION
            );
        }

        return $data;
    }

    ############################################################################
    ########################## METODOS DE CONSUMO WS ###########################
    ############################################################################

    /**
     * Consume la informacion del Web Service a travez del metodo GET
     * @param String $url
     */
    private function consumir_WebService($url) {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json; charset=utf-8'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        $result = curl_exec($ch);
        $getinfo = curl_getinfo($ch);

        $informe = array(
            "mensaje" => $result,
            "http_code" => $getinfo['http_code'],
        );

        return $informe;
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    ############################################################################
    ##########################     METODOS VARIOS    ###########################
    ############################################################################

    /**
     * Metodo que reemplaza por NULL cuando un valor es vacio
     * @param String $valor
     * @return String
     */
    private function valor($valor) {

        $resultado = (trim($valor) !== '') ? $valor : 'NULL';

        return $resultado;
    }

    /**
     * Metodo que completa ceros en una cadena
     * @param String $valor
     * @param int $long
     * @return String
     */
    private function CompletarCeros($valor, $long = 0) {

        return str_pad($valor, $long, '0', STR_PAD_LEFT);
    }

    /**
     * Metodo que crea un log de las novedades presentadas en el proceso de consumo de la informacion
     * @param String $ruta
     * @param String $descripcion
     * @param String $nuevo_log
     */
    public function crear_log($ruta, $descripcion, $nuevo_log = '') {

        $directorio = '../log/' . $ruta;

        if (!is_dir($directorio)) {

            mkdir($directorio, 0777, true);
        }

        $archivo = fopen($directorio . '/' . $ruta . '.txt', "a") or die("Archivo inaccesible!");

        //Si viene con encabezado agrego un separador entre consumos
        if ($nuevo_log == '1') {

            fwrite($archivo, PHP_EOL . PHP_EOL . "###########################################################" . PHP_EOL);
        }

        fwrite($archivo, $descripcion . "\r\n");
        fclose($archivo);
    }

}
