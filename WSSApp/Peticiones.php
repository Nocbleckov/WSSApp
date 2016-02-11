<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once ("./WebService.php");

$json = array();

$numPeticion = $_POST['numPeticion'];
//$numPeticion = 11;
$peticion = new WebService();

switch ($numPeticion) {
    case 1:
        $nick = $_POST['nick'];
        //$nick = "f.medina";
        $pass = $_POST['pass'];
        //$pass = "manager";
        $json = $peticion->obtenerUsuario($nick, $pass);
        break;
    case 2:
        $json = $peticion->obtenerPuntos();
        break;
    case 3:
        $idRuta = $_POST['idRuta'];
        //$idRuta = " 14";
        $json = $peticion->obtenerPuntosEncuesta($idRuta);
        break;
    case 4:
        $idBrigada = $_POST['idBrigada'];
        $idUsuario = $_POST['idUsuario'];
        $json = $peticion->obtenerPuntosBrigada($idBrigada, $idUsuario);
        break;
    case 5:    
        $peticion->generarGrafo($nodos, $nodoA);
        break;
    case 6:
        $idUsuario = $_POST['idUsuario'];
        $latitud = $_POST['lat'];
        $longitud = $_POST['lng'];
        $peticion->registrarPosicion($idUsuario, $latitud, $longitud);
        break;
    case 7:
        $idUsuario =  $_POST['idUsuario'];
        $idUsuario = 5;
        $json = $peticion->obtenerRutas($idUsuario);
        break;
    case 8:
        $peticion->imagenRuta();
        break;
    case 9:
        $arreglo = $_POST['coord'];
        $json = $peticion->pesoMax($arreglo);
        break;
    case 10:
        
        $cadenaRuta = $_POST['cadenaRuta'];
        $siglas = $_POST['siglas'];
        $tiempoManual = $_POST['tiempoManual'];
        $idAccionCambio = $_POST['idAccionCambio'];
        $idUsuario = $_POST['idUsuario'];
        $idRutaActual = $_POST['idRutaActual'];
        $arregloPuntos = $_POST['arregloPuntos'];
        
        /*$cadenaRuta = "k|rdCrimoRu@cAsAdAyApAu@gAmBmCo@h@eBzAaAx@]qA[qAKw@[RkG|FS?aAd@EMY}@X|@DL`Ae@HS\]pF_Fj@[LEpCeCbAw@zFcFvF}EbKyId@[FAzBTnCFF?CUAU?c@r@eIaD[ZgDPuBQtBaIy@kAMmB|AsFvEgDpC_AsAeDuE";
        $siglas = "AGS1";
        $tiempoManual = "133";
        $idAccionCambio = "345";
        $idUsuario = "5";
        $idRutaActual = "14";*/
        
        $json = $peticion->updateRutaUsuario($cadenaRuta, $siglas, $tiempoManual, $idAccionCambio, $idUsuario, $idRutaActual,$arregloPuntos);
        
        break;
    case 11:
        
        //$idRuta = 16;
        //$arregloPuntos = '[{"idRuta":1},{"idRuta":27},{"idRuta":26},{"idRuta":29},{"idRuta":28},{"idRuta":30}]';
        
        $peticion->asignarPuntosRuta($idRuta, $arregloPuntos);
        break;
    case 12:
        break;
    case 13:
        break;
}

echo $json;