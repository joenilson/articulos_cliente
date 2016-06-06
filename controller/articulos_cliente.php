<?php
/*
 * Copyright (C) 2016 Joe Nilson <joenilson at gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
require_model('factura_cliente.php');
require_model('linea_factura_cliente.php');
require_model('articulos.php');
require_model('cliente.php');
/**
 * Description of articulos_cliente
 *
 * @author Joe Nilson <joenilson at gmail.com>
 */
class articulos_cliente extends fs_controller {
    public $cliente;
    public $articulo;
    public $articulos;
    public $facturas;
    public $lineasfacturas;
    public $total_articulos;
    public $resultados;
    public $resultados_cantidad;
    public $fecha_inicio;
    public $fecha_fin;
    public function __construct() {
        parent::__construct(__CLASS__, 'Articulos Cliente', 'ventas', FALSE, FALSE, FALSE);
    }
    protected function private_core() {
        $this->clientes = new cliente();
        $this->facturas = new factura_cliente();
        $this->lineasfacturas = new linea_factura_cliente();
        $this->total_articulos = 0;
        $this->share_extensions();
        
        //recibimos el código del cliente
        if(isset($_REQUEST['cod']) AND !empty($_REQUEST['cod'])){
            $this->cliente = $this->clientes->get($_REQUEST['cod']);
            $this->articulos = $this->lineasfacturas->search_from_cliente($this->cliente->codcliente);
            $this->total_articulos = count($this->articulos);
            $listadoArticulos = array();
            $cantidadArticulos = array();
            $importeArticulos = array();
            //agrupamos los valores
            foreach ($this->articulos as $linea){
                if(!isset($listadoArticulos[$linea->referencia])){
                    $listadoArticulos[$linea->referencia] = $linea->descripcion;
                }
                if(!isset($cantidadArticulos[$linea->referencia])){
                    $cantidadArticulos[$linea->referencia] = 0;
                }
                if(!isset($importeArticulos[$linea->referencia])){
                    $importeArticulos[$linea->referencia] = 0;
                }
                //$listadoArticulos[$linea->referencia] = $linea->descripcion;
                $cantidadArticulos[$linea->referencia] += $linea->cantidad;
                $importeArticulos[$linea->referencia] += $linea->pvptotal;
            }
            
            //Generamos los resultados finales
            $lista = array();
            foreach($listadoArticulos as $ref => $desc){
                $item = new stdClass();
                $item->ref = $ref;
                $item->desc = $desc;
                $item->cantidad = $cantidadArticulos[$ref];
                $item->importe = $importeArticulos[$ref];
                $lista[]=$item;
            }
            $this->resultados = $lista;
            $this->resultados_cantidad = count($lista);
        }
    }
    
    public function fechas($fecha,$tipo){
        $d = new DateTime($fecha);
        if($tipo == 'inicio'){
            $nueva_fecha = $d->modify('first day of this month')->format('d-m-Y');
        }elseif($tipo == 'fin'){
            $nueva_fecha = $d->modify('last day of this month')->format('d-m-Y');
        }
        return $nueva_fecha;
    }
    
    //Agregamos el tab a ventas_cliente
    public function share_extensions(){
        $fsxet = new fs_extension();
        $fsxet->name = 'tab_articulos_cliente';
        $fsxet->from = __CLASS__;
        $fsxet->to = 'ventas_cliente';
        $fsxet->type = 'tab';
        $fsxet->text = '<span class="glyphicon glyphicon-list" aria-hidden="true"></span>'
                . '<span class="hidden-xs">  Artículos</span>';
        $fsxet->save();
    }
}
