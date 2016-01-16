<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Vertice
 *
 * @author DESARROLLO
 */
class Vertice extends stdClass {
    
    private $origen;
    private $destino;
    private $valor;
    
    public function __construct($origen,$destino,$valor){
        $this->origen = $origen;
        $this->destino = $destino;
        $this->valor = $valor;
    }
    
    public function getOrigen(){
        return $this->origen;
    }
    
    public function getDestino(){
        return $this->destino;
    }
    
    public function getValor(){
        return $this->valor;
    }
}

