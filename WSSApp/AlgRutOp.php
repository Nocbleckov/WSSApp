<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AlgRutOp
 *
 * @author DESARROLLO
 */
require_once ("Nodo.php");
require_once ("Vertice.php");

class AlgRutOp {

    public $destinos = array();
    public $nodos = array();
    private $rutOp = array();

    public function __construct($nodos) {
        //echo '<br>//Constructor//';
        $this->nodos = $nodos;
        /*foreach ($nodos as $nodo) {
            echo '<br>' . $nodo->getNombre();
            foreach ($nodo->getDestinos() as $destino) {
                echo '<br>  :' . $destino->getDestino()->getNombre() . '= ' . $destino->getValor();
            }
            echo '<br>';
        }*/
    }

    public function iniciar($nodoOrigen) {
        $nodoOrigen->setVisitado(TRUE);
        $this->borrarDestinos($nodoOrigen);
        $this->destinos = array_merge($this->destinos, $nodoOrigen->getDestinos());
        if ($this->todosVisitados() == FALSE) {
            if (count($this->destinos) > 0) {
                $this->ordenarDestinos();
                $origenV = $this->destinos[0]->getOrigen();
                $destinoV = $this->destinos[0]->getDestino();
                $this->rutOp[] = $this->destinos[0];
                //echo '<br>' . $origenV->getNombre() . '-' . $destinoV->getNombre() . " = " . $this->destinos[0]->getValor();
                $nodoOrigen = $this->destinos[0]->getDestino();
                $this->iniciar($nodoOrigen);
            } else {
                //echo '<br>' . "fin algoritmo^";
            }
        } else {
            //echo '<br> fin algoritmo*';
        }
    }

    public function borrarDestinos($nodoDestino) {
        /* echo '<br>//Antes de Borrado//';
          foreach ($this->destinos as $destino) {
          echo '<br>'.$destino->getOrigen()->getNombre().'-'.$destino->getDestino()->getNombre().'= '.$destino->getValor();
          } */


        $this->destinos = array_filter($this->destinos, function ($vertice) use ($nodoDestino) {
            return $this->destIgual($vertice, $nodoDestino);
        });

        foreach ($this->nodos as $nodo) {
            $nodo->eliminarDestinos($nodoDestino);
        }

        /* echo '<br>//Despues de Borrado//';
          foreach ($this->destinos as $destino) {
          echo '<br>'.$destino->getOrigen()->getNombre().'-'.$destino->getDestino()->getNombre().'= '.$destino->getValor();
          }
          echo '<br>';
          echo '///////'; */
    }

    public function destIgual($vertice, $nodoDestino) {
        if ($vertice->getDestino() == $nodoDestino) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function todosVisitados() {
        foreach ($this->nodos as $nodo) {
            if ($nodo->getVisitado() == false) {
                return FALSE;
            }
        }
        return TRUE;
    }

    public function ordenarDestinos() {

        /*echo '<br>//Antes de ordenar//';
        foreach ($this->destinos as $destino) {
            echo '<br>' . $destino->getOrigen()->getNombre() . '-' . $destino->getDestino()->getNombre() . '= ' . $destino->getValor();
        }*/
        
        $aux = NULL;
        for ($i = 0; $i < count($this->destinos) - 1; $i++) {
            for ($x = $i + 1; $x < count($this->destinos); $x++) {
                if ($this->destinos[$x]->getValor() < $this->destinos[$i]->getValor()) {
                    $aux = $this->destinos[$i];
                    $this->destinos[$i] = $this->destinos[$x];
                    $this->destinos[$x] = $aux;
                }
            }
        }
        
        /*echo '<br>//Despues de Ordenar//';
        foreach ($this->destinos as $destino) {
            echo '<br>' . $destino->getOrigen()->getNombre() . '-' . $destino->getDestino()->getNombre() . '= ' . $destino->getValor();
        }
        echo '<br>';
        echo '///////';*/
    }
    
    public function getRutOp(){
        return $this->rutOp;
    }

}
