<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Nodo
 *
 * @author DESARROLLO
 */
class Nodo extends stdClass {

    private $id;
    private $nombre;
    private $latitud;
    private $longitud;
    private $destinos = array();
    private $visitado = false;
    private $conectado = NUll;

    public function __construct($nombre, $latitud, $longitud, $id) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->latitud = $latitud;
        $this->longitud = $longitud;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getId() {
        return $this->id;
    }

    public function getDestinos() {
        return $this->destinos;
    }

    public function getLat() {
        return $this->latitud;
    }

    public function getLng() {
        return $this->longitud;
    }

    public function getVisitado() {
        return $this->visitado;
    }

    public function getConectado() {

        if ($this->conectado == NULL) {
            if (count($this->destinos) > 0) {
                $this->conectado = TRUE;
            } else {
                $this->conectado = FALSE;
            }
        }
        return $this->conectado;
    }

    public function setConectado($conectado) {
        $this->conectado = $conectado;
    }

    public function setVisitado($visitado) {
        $this->visitado = $visitado;
    }

    public function setDestinos($destinos) {
        $this->destinos = NULL;
        $this->destinos = $destinos;
    }

    public function valorDestinos() {
        $total = 0;
        foreach ($this->destinos as $valor) {
            $total = $total + $valor->getValor();
        }
        return $total;
    }

    public function valorDestinosDes($promedio) {
        $total = 0;

        foreach ($this->destinos as $valor) {
            $total = $total + pow(($valor->getValor() - $promedio), 2);
        }

        return $total;
    }

    public function ordenar() {
        $aux = NULL;
        for ($i = 0; $i < count($this->destinos) - 1; $i++) {
            for ($f = $i + 1; $f < count($this->destinos); $f++) {
                if (!$this->destinos[$f]->getValor() < $this->destinos[$i]->getValor()) {
                    $aux = $this->destinos[$i];
                    $this->destinos[$i] = $this->destinos[$f];
                    $this->destinos[$f] = $aux;
                }
            }
        }
        $this->filtrar();
    }

    private function filtrar() {
        $tolerancia = $this->destinos[0]->getValor();

        function mayorA($destino, $tolerancia) {
            $valor = $destino->getValor();
            if ($valor > $tolerancia) {
                return false;
            } else {
                return true;
            }
        }

        $this->destinos = array_filter($this->destinos, function($destino) use ($tolerancia) {
            return mayorA($destino, $tolerancia);
        });
        $this->conectado = TRUE;
    }

    public function agregarDestino($destino, $valor) {
        $this->destinos[] = new Vertice($this, $destino, $valor);
    }

    public function eliminarDestinos($nodoDestino) {



        $this->destinos = array_filter($this->destinos, function($vertice) use ($nodoDestino) {
            return $this->igualA($vertice, $nodoDestino);
        });
    }

    public function igualA($vertice, $nodoDestino) {

        if ($vertice->getDestino() == $nodoDestino) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

}
