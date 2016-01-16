<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of newPHPClass
 *
 * @author DESARROLLO
 */
class Conexion {
    
    public $hostname_localhost = "sysintpro.com.mx";
    public $database_localhost = "senda";
    public $username_localhost = "g214";
    public $password_localhost = "Desarrollo_G214_D1";
    
    function conn() {
        try {
            $conn = new PDO("mysql:host=$this->hostname_localhost;dbname=$this->database_localhost", $this->username_localhost, $this->password_localhost, array('charset' => 'utf8'));
        } catch (PDOException $e) {
            echo 'no se puede conectar';
            echo '<br>' . $e;
            exit;
        }
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->query('SET CHARACTER SET utf8');
        return $conn;
    }
    
}
