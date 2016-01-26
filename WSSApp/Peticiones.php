<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once ("./WebService.php");

$json = array();

$numPeticion = $_POST['numPeticion'];
//$numPeticion = 7;
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
        break;
    case 10:
        break;
}

echo $json;