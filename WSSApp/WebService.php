<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once ("Conexion.php");
require_once ("Nodo.php");
require_once ("Vertice.php");
require_once ("AlgRutOp.php");

class WebService extends Conexion {

    public function obtenerUsuario($nick, $pass) {
        $query = "Select u.idUsuario,"
                . "ub.idBrigada,"
                . "concat(u.nombre,' ',u.primerApellido,' ',u.segundoApellido) as nombre,"
                . " u.idPerfil,"
                . "u.nickname,"
                . "u.pass,"
                . "e.nombre as estado,"
                . "m.url as fotoUrl "
                . "from usuario_brigada ub "
                . "right join usuario u "
                . "on u.idUsuario = ub.idUsuario "
                . "inner join estado e "
                . "on e.idEstado = u.idEstado "
                . "inner join media m "
                . "on m.idMedia = u.idFoto "
                . "where u.nickname = :nick "
                . "and u.pass = :pass;";
        $conexion = $this->conn();
        $sql = $conexion->prepare($query);
        $sql->execute(array(":nick" => $nick, ":pass" => $pass));
        if ($sql->rowCount()) {
            while ($row = $sql->fetchAll(PDO::FETCH_CLASS)) {
                $json["usuario"] = $row;
            }
            $json['respuesta'] = [['echo' => TRUE]];
        } else {
            $json['respuesta'] = [['echo' => FALSE]];
        }

        return json_encode($json);
    }

    public function obtenerPuntos() {
        $quey = "Select * from punto;";
        $conexion = $this->conn();
        $sql = $conexion->prepare($quey);
        $sql->execute();

        if ($sql->rowCount()) {
            while ($row = $sql->fetchAll(PDO::FETCH_CLASS)) {
                $json['puntos'] = $row;
            }
            $json['respuesta'] = [['echo' => TRUE]];
        } else {
            $json['respuesta'] = [['echo' => FALSE]];
        }

        return json_encode($json);
    }

    public function obtenerRutas($idUsuario) {
        $query = "Select r.idRuta,"
                . "r.cadenaRuta,"
                . "r.municipio as siglas,"
                . "m.nombre as municipio,"
                . " e.nombre as estado ,"
                . "r.tiempoManual,"
                . "r.tiempoAutomatico,"
                . "r.estatus,"
                . "r.idAccionRuta as original,"
                . "r.idAccionCambio as version "
                . "from ruta r inner "
                . "join rutaUsuario ru "
                . "on r.idRuta = ru.idRuta "
                . "inner join municipio m "
                . "on m.siglas = r.municipio "
                . "inner join estado e "
                . "on e.idEstado = m.idEstado "
                . "where ru.idUsuario = :idUsuario "
                . "and r.vigente = 1;";
        $conexion = $this->conn();
        $sql = $conexion->prepare($query);
        $sql->execute(array(":idUsuario" => $idUsuario));


        if ($sql->rowCount()) {
            while ($row = $sql->fetchAll(PDO::FETCH_CLASS)) {
                $json['rutas'] = $row;
                /* echo '<br><br>' . var_dump($row) . '<br><br>'; */
            }

            for ($i = 0; $i < count($json['rutas']); $i++) {
                $idRuta = $json['rutas'][$i]->idRuta;
                $cadenaRuta = $json['rutas'][$i]->cadenaRuta;
                $destinos = $this->obtenerPuntosEncuesta($idRuta);

                /* echo "<br>$i.__idRuta,<br>" . var_dump($idRuta) . '<br><br>';
                  echo "<br>$i.__cadenaRuta<br>" . var_dump($cadenaRuta) . '<br><br>';
                  echo "<br>$i.__destino<br>" . var_dump($destinos) . '<br><br>'; */

                $aristasCentro = $this->aristasCentro($destinos['puntos']);
                $centro = $aristasCentro->centro;
                $SI = $aristasCentro->SI;
                $SD = $aristasCentro->SD;
                $II = $aristasCentro->II;
                $ID = $aristasCentro->ID;

                $json['rutas'][$i]->destinos = $destinos;
                $json['rutas'][$i]->centro = $centro;
                $json['rutas'][$i]->sI = $SI;
                $json['rutas'][$i]->sD = $SD;
                $json['rutas'][$i]->iI = $II;
                $json['rutas'][$i]->iD = $ID;
                $json['rutas'][$i]->rutaImagen = $this->imagenRuta($centro, $cadenaRuta);
            }

            $json['respuesta'] = [['echo' => TRUE]];
        } else {
            $json['respuesta'] = [['echo' => FALSE]];
        }

        return json_encode($json);
    }

    private function maxIdRuta() {


        $query = "Select max(idRuta)+1 as max from ruta;";
        $conexion = $this->conn();
        $sql = $conexion->prepare($query);

        $sql->execute();

        $max = $sql->fetchAll(PDO::FETCH_CLASS);

        return $max[0]->max;
    }

    private function maxIdRutaUsuario() {

        $query = "select max(idRutaUsuario)+1 as max from rutaUsuario;";
        $conexion = $this->conn();
        $sql = $conexion->prepare($query);

        $sql->execute();

        $max = $sql->fetchAll(PDO::FETCH_CLASS);

        return $max[0]->max;
    }

    public function pesoMax($arreglo) {

        $peso = 0;

        $json = json_decode($arreglo);

        foreach ($json as $valor) {
            $peso = $peso + $this->staticMapPeso($valor->x, $valor->y, $valor->zoom);
        }
        return ($peso / (1024 * 1024));
    }

    public function puntoRutaMax() {
        $query = "Select max(idpuntoRuta)+1 as max from puntoRuta;";

        $conexion = $this->conn();
        $sql = $conexion->prepare($query);
        $sql->execute();
        $max = $sql->fetchAll(PDO::FETCH_CLASS);

        return $max[0]->max;
    }

    public function asignarPuntosRuta($idRuta, $arregloPuntos) {
        $conexion = $this->conn();
        $json = json_decode($arregloPuntos);
        
        foreach ($json as $punto) {
            $idPunto = $punto->idRuta;
            $idMax = $this->puntoRutaMax();
            $query = "INSERT INTO puntoRuta (idpuntoRuta, idRuta, idPunto) VALUES ('$idMax', '$idRuta', $idPunto);";
            $sql = $conexion->prepare($query);
            $sql->execute();
        }
        
        return $json;
    }

    public function inHabilitarRuta($idRuta) {

        $query = "UPDATE ruta SET vigente =0 WHERE idRuta = '$idRuta';";
        $conexion = $this->conn();
        $sql = $conexion->prepare($query);
        $sql->execute();
    }

    public function updateRutaUsuario($cadenaRuta, $siglas, $tiempoManual, $idAccionCambio, $idUsuario, $idRutaActual, $arregloPuntos) {
        $idRutaUsuario = $this->maxIdRutaUsuario();
        $idRuta = $this->updateRuta($cadenaRuta, $siglas, $tiempoManual, $idAccionCambio, $idUsuario);
        $this->inHabilitarRuta($idRutaActual);


        $query = "INSERT INTO rutaUsuario (idRutaUsuario, idRuta, idUsuario) VALUES ($idRutaUsuario, $idRuta, $idUsuario);";

        $conexion = $this->conn();
        $sql = $conexion->prepare($query);
        $sql->execute();

        $json = $this->asignarPuntosRuta($idRuta, $arregloPuntos);

        return var_dump($json);
    }

    public function updateRuta($cadenaRuta, $siglas, $tiempoManual, $idAccionCambio, $idUsuario) {
        $idRuta = $this->maxIdRuta();

        $vigente = 1;
        $estatus = "DISPONIBLE";

        $descripcion = "Actualizacion de Ruta";
        $tipoAccion = "REGISTRO";
        $idPagina = 1;
        $idAccionRuta = $this->registrarAccion($idUsuario, $tipoAccion, $idPagina, $descripcion);


        $query = "INSERT INTO ruta (idRuta, vigente, cadenaRuta, municipio, tiempoManual, estatus, idAccionRuta, idAccionCambio) VALUES ($idRuta, $vigente,'$cadenaRuta','$siglas' , $tiempoManual ,'$estatus', $idAccionRuta, $idAccionCambio);";
        $conexion = $this->conn();
        $sql = $conexion->prepare($query);

        $sql->execute();

        return $idRuta;
    }

    protected function aristasCentro($destinos) {

        for ($i = 0; $i < count($destinos); $i++) {

            $x[] = $destinos[$i]->latitud;
            $y[] = $destinos[$i]->longitud;
        }

        $xS = $this->menorMayor($x);
        $yS = $this->menorMayor($y);

        /* echo '<br><br>'.var_dump($xS).'<br><br>';
          echo '<br><br>'.var_dump($yS).'<br><br>'; */

        $xC = (($xS->menor - $xS->mayor) / 2) + $xS->mayor;
        $yC = (($yS->menor - $yS->mayor) / 2) + $yS->mayor;

        $aristaSI = new stdClass();
        $aristaSD = new stdClass();
        $aristaII = new stdClass();
        $aristaID = new stdClass();
        $centro = new stdClass();

        $aristaSD->lat = (double) $xS->mayor;
        $aristaSD->lng = (double) $yS->mayor;

        $aristaII->lat = (double) $xS->menor;
        $aristaII->lng = (double) $yS->menor;

        $aristaSI->lat = (double) $xS->menor;
        $aristaSI->lng = (double) $yS->mayor;

        $aristaID->lat = (double) $xS->mayor;
        $aristaID->lng = (double) $yS->menor;


        $centro->lat = $xC;
        $centro->lng = $yC;

        $aristasCentro = new stdClass();
        $aristasCentro->SI = $aristaSI;
        $aristasCentro->SD = $aristaSD;
        $aristasCentro->II = $aristaII;
        $aristasCentro->ID = $aristaID;
        $aristasCentro->centro = $centro;


        return $aristasCentro;
    }

    protected function menorMayor($arreglo) {
        $menor = $arreglo[0];
        $mayor = $arreglo[0];

        foreach ($arreglo as $valor) {
            if ($valor < $menor) {
                $menor = $valor;
            } else if ($valor > $mayor) {
                $mayor = $valor;
            }
        }
        $menorMayor = new stdClass();
        $menorMayor->menor = $menor;
        $menorMayor->mayor = $mayor;
        /* echo '<br>'.var_dump($arreglo);
          echo '<br>'.var_dump($menor);
          echo '<br>'.var_dump($mayor);
          echo '<br><br>'.  var_dump($menorMayor).'<br><br>'; */

        return $menorMayor;
    }

    public function obtenerPuntosEncuesta($idRuta) {
        $query = "Select  r.idRuta,"
                . "p.*,"
                . "r.cadenaRuta "
                . "from puntoRuta pr "
                . "inner join ruta r "
                . "on pr.idRuta = r.idRuta "
                . "inner join rutaUsuario ru "
                . "on r.idRuta = ru.idRuta "
                . "inner join punto p "
                . "on pr.idPunto = p.idPunto "
                . "where r.idRuta = :idRuta;";
        $conexion = $this->conn();
        $sql = $conexion->prepare($query);
        $sql->execute(array(":idRuta" => $idRuta));

        if ($sql->rowCount()) {
            while ($row = $sql->fetchAll(PDO::FETCH_CLASS)) {
                $json['puntos'] = $row;
            }
            foreach ($json['puntos'] as $punto) {
                $coord = new stdClass();
                $coord->lat = $punto->latitud;
                $coord->lng = $punto->longitud;
                $rutaImagen = $this->imagenPunto($coord);
                $punto->rutaImagen = $rutaImagen;
            }
        } else {
            $json['respuesta'] = [['echo' => FALSE]];
        }

        return $json;
    }

    Public function obtenerPuntosBrigada($idBrigada, $idUsuario) {
        $query = "Select p.*,"
                . " r.cadenaRuta "
                . "from puntoRuta pr "
                . "inner join ruta r "
                . "on pr.idRuta = r.idRuta "
                . "inner join rutaUsuario ru "
                . "on r.idRuta = ru.idRuta "
                . "inner join punto p "
                . "on pr.idPunto = p.idPunto "
                . "inner join punto_brigada pb "
                . "on pb.idPunto = pr.idPunto "
                . "where pb.idBrigada = :idBrigada "
                . "and ru.idUsuario != :idUsuario ;";

        $conexion = $this->conn();
        $sql = $conexion->prepare($query);
        $sql->execute(array(":idBrigada" => $idBrigada, ":idUsuario" => $idUsuario));

        if ($sql->rowCount()) {
            while ($row = $sql->fetchAll(PDO::FETCH_CLASS)) {
                $json['puntos'] = $row;
            }
            $json['respuesta'] = [['echo' => TRUE]];
        } else {
            $json['respuesta'] = [['echo' => FALSE]];
        }

        return json_encode($json);
    }

    public function algRut($idUsuario) {
        $json = $this->obtenerPuntosEncuesta($idUsuario);
        $json = json_decode($json);
        var_dump($json->puntos);
    }

    public function distanciasPuntos($nOrigen, $nDestino) {
        $nombreOrg = $nOrigen->getNombre();
        $nombreDst = $nDestino->getNombre();
        $latO = $nOrigen->getLat();
        $lngO = $nOrigen->getLng();
        $latD = $nDestino->getLat();
        $lngD = $nDestino->getLng();

        $response = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$latO,$lngO&destinations=$latD,$lngD&mode=walking&language=es-ES&key=AIzaSyCN9dweEHH0yQXVVLyuCTxa_Es1Vk0gzJY";
        $url = file_get_contents($response);

        $json = json_decode($url);

        $distancia = $json->rows[0]->elements[0]->distance->value;

        $nOrigen->agregarDestino($nDestino, $distancia);
        return $distancia;
    }

    public function constructorUrl($arregloNodos) {

        $waypoints = '&waypoints=';
        $url = 'https://maps.googleapis.com/maps/api/directions/json?key=AIzaSyCN9dweEHH0yQXVVLyuCTxa_Es1Vk0gzJY&mode=walking';

        for ($i = 0; $i < count($arregloNodos); $i++) {
            if ($i == 0) {
                $origen = '&origin=' . $arregloNodos[$i]->getLat() . ',' . $arregloNodos[$i]->getLng();
            } else if ($i == count($arregloNodos) - 1) {
                $destino = '&destination=' . $arregloNodos[$i]->getLat() . ',' . $arregloNodos[$i]->getLng();
            } else {
                $waypoints = $waypoints . $arregloNodos[$i]->getLat() . ',' . $arregloNodos[$i]->getLng() . "|";
            }
        }

        $respuesta = $url . $origen . $destino . $waypoints;

        //echo $respuesta;

        $urlR = file_get_contents($respuesta);
        $json = json_decode($urlR);

        $cadena = $json->routes[0]->overview_polyline->points;

        return $cadena;
    }

    public function generarGrafo($nodos, $nodoInicio) {

        /* $nodos = array();

          $nodoA = new Nodo("A", 21.8852562, -102.2915677, "1");
          $nodoB = new Nodo("B", 21.89814, -102.30992, "2");
          $nodoC = new Nodo("C", 21.89423, -102.2955, "3");
          $nodoD = new Nodo("D", 21.89397, -102.29638, "4");

          $nodos[] = $nodoA;
          $nodos[] = $nodoB;
          $nodos[] = $nodoC;
          $nodos[] = $nodoD; */

        foreach ($nodos as $val) {
            $origen = $val;
            $val->setVisitado(TRUE);
            foreach ($nodos as $value) {
                if ($origen != $value) {
                    if ($value->getVisitado() == FALSE) {
                        $this->distanciasPuntos($origen, $value);
                    }
                }
            }
        }
        $tolerancia = $this->obtenerTolerancia($nodos);
        $nodos = $this->dscmnrVertices($nodos, $tolerancia);
        $this->todosConectados($nodos);
        $this->limpiarVisitados($nodos);
        foreach ($nodos as $nodo) {
            $nodo->setVisitado(TRUE);
            foreach ($nodo->getDestinos() as $destinos) {
                $valor = $destinos->getValor();
                if ($destinos->getDestino()->getVisitado() == FALSE) {
                    $destinos->getDestino()->agregarDestino($nodo, $valor);
                }
            }
        }

        /* //finalCerrado**
          echo 'Grafo Final';
          foreach ($nodos as $value) {
          echo '<br>';
          echo 'Origen :' . $value->getNombre();
          foreach ($value->getDestinos() as $val) {
          echo '<br>';
          echo 'Destino: ' . $val->getDestino()->getNombre() . "= " . $val->getValor();
          }
          echo '<br>';
          }
          //

          echo '<br>/////////////////<br>'; */

        $this->limpiarVisitados($nodos);


        $algRut = new AlgRutOp($nodos);

        $algRut->iniciar($nodoInicio);

        $rutOp = $algRut->getRutOp();

        /* '<br>////Ruta Optima////';
          foreach ($rutOp as $destinos){
          echo '<br>'.$destinos->getOrigen()->getNombre().'-'.$destinos->getDestino()->getNombre().'= '.$destinos->getValor();
          }
          echo '<br>///<br>'; */

        $arregloNodos = $this->arregloNodos($rutOp);

        /* echo '<br>////Nodos Ordenados////';
          foreach ($arregloNodos as $nodo) {
          echo '<br>'.$nodo->getNombre().' lat:'.$nodo->getLat().' lng:'.$nodo->getLng();
          }
          echo '<br>////<br>'; */

        //echo '<br>'.$this->constructorUrl($arregloNodos).'<br>';

        return $this->constructorUrl($arregloNodos);
    }

    public function arregloNodos($rutOp) {
        $arregloNodos = array();
        for ($i = 0; $i < count($rutOp); $i++) {

            if ($i == 0) {
                $arregloNodos[] = $rutOp[$i]->getOrigen();
                $arregloNodos[] = $rutOp[$i]->getDestino();
            } else {

                $arregloNodos[] = $rutOp[$i]->getDestino();
            }
            /* $arregloNodos[] = $rutOp[$i]->getOrigen();  
              if($i == count($rutOp)-1){
              $arregloNodos[] = $rutOp[$i]->getDestino();
              } */
        }
        return $arregloNodos;
    }

    public function obtenerTolerancia($nodos) {
        $suma = 0;
        $max = 0;
        foreach ($nodos as $value) {
            $suma = $suma + $value->valorDestinos();
            $max = $max + count($value->getDestinos());
        }
        $promedio = $suma / $max;
        $sumaIte = 0;
        foreach ($nodos as $val) {
            //echo '<br>Valor '.$val->valorDestinos();
            //echo '<br>Promedio '.$promedio;
            //echo '<br>Max '.$max;           
            $sumaIte = $sumaIte + $val->valorDestinosDes($promedio);
            //echo '<br>'.$sumaIte;
        }
        /* echo '<br>/////';
          echo sqrt($sumaIte/6); */
        $total = sqrt($sumaIte / $max) + $promedio;
        return $total;
    }

    public function dscmnrVertices($nodos, $tolerancia) {

        function mayor($destino, $tolerancia) {
            $valor = $destino->getValor();
            if ($valor > $tolerancia) {
                return FALSE;
            } else {
                $destino->getDestino()->setConectado(TRUE);
                return TRUE;
            }
        }

        $max = count($nodos);

        for ($i = 0; $i < $max; $i = $i + 1) {

            $destinos = $nodos[$i]->getDestinos();

            $destinos = array_filter($destinos, function ($destino) use($tolerancia) {
                return mayor($destino, $tolerancia);
            });

            $nodos[$i]->setDestinos($destinos);
        }

        return $nodos;
    }

    public function limpiarVisitados($nodos) {

        foreach ($nodos as $nodo) {
            $nodo->setVisitado(FALSE);
        }
    }

    public function todosConectados($nodos) {
        foreach ($nodos as $nodo) {
            if ($nodo->getConectado() == FALSE) {
                foreach ($nodos as $val) {
                    if ($nodo != $val) {
                        $this->distanciasPuntos($nodo, $val);
                    }
                }

                $nodo->ordenar();
            } else {
                
            }
        }
    }

    public function obtenerIdMaxAccion() {
        $query = "Select max(idAccion) + 1 as max from accion;";

        $conexion = $this->conn();
        $sql = $conexion->prepare($query);
        $sql->execute();

        if ($sql->rowCount()) {
            while ($row = $sql->fetchAll(PDO::FETCH_CLASS)) {
                $max = $row[0]->max;
            }
        }
        return $max;
    }

    public function registrarAccion($idUsuario, $tipoAccion, $idPagina, $descripcion) {

        $idAccion = $this->obtenerIdMaxAccion();
        $fecha = date('Y-m-d');

        $quey = "INSERT INTO accion (`idAccion`, `idUsuario`, `fecha`, `tipoAccion`, `idPagina`, `descripcion`) "
                . "VALUES (:idAccion, :idUsuario, :fecha, :tipoAccion, :idPagina, :descripcion);";
        $conexion = $this->conn();
        $sql = $conexion->prepare($quey);
        $sql->execute(array(':idAccion' => $idAccion, ':idUsuario' => $idUsuario, ':fecha' => $fecha, ':tipoAccion' => $tipoAccion, ':idPagina' => $idPagina, ':descripcion' => $descripcion));

        return $idAccion;
    }

    public function registrarPosicion($idUsuario, $latitud, $longitud) {
        $tipoAccion = 'REGISTRO';
        $idPagina = 3;
        $descripcion = 'Posicion de Encuestador: ' . $idUsuario;
        $idAccion = $this->registrarAccion($idUsuario, $tipoAccion, $idPagina, $descripcion);
        $query = 'INSERT INTO posicion (`idUsuario`, `latitud`, `longitud`, `idAccion`) VALUES (:idUsuario,:latidud, :longitud, :idAccion);';
        $conexion = $this->conn();
        $sql = $conexion->prepare($query);
        $sql->execute(array(':idUsuario' => $idUsuario, ':latidud' => $latitud, ':longitud' => $longitud, ':idAccion' => $idAccion));
    }

    public function imagenRuta($central, $cadenaRuta) {
        $central = $central->lat . "," . $central->lng;
        $url = "https://maps.googleapis.com/maps/api/staticmap?&scale=4" . "&%center=" . $central . "&zoom=15&size=600x200&path=weight:5%7Ccolor:blue%7Cenc:" . $cadenaRuta . "&key=AIzaSyCN9dweEHH0yQXVVLyuCTxa_Es1Vk0gzJY";

        //$file = fopen($url,'r');
        //$final = base64_decode($file);

        return $url;
    }

    public function imagenPunto($coord) {
        $coordF = $coord->lat . "," . $coord->lng;
        $url = "https://maps.googleapis.com/maps/api/streetview?size=1200x300&fov=120&location=$coordF&pitch=4&key=AIzaSyCN9dweEHH0yQXVVLyuCTxa_Es1Vk0gzJY";
        return $url;
    }

    public function staticMapPeso($x, $y, $zoom) {

        $url = "http://mt0.google.com/vt/lyrs=y&hl=es&x=$x&y=$y&z=$zoom&scale=4&s=Galileo";
        $curl = curl_init($url);


        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:13.0) Gecko/20100101 Firefox/13.0.1');

        $data = curl_exec($curl);
        $result = curl_getinfo($curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($curl);

        return $result;
    }

}
