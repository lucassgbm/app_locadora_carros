<?php

namespace App\Repositories;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractRepository {

    function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function selectAtributosRegistrosRelacionados($atributos){
        
        $this->model = $this->model->with($atributos);
        // a query está sendo montada
    }

    public function filtro ($filtros){

        $filtros = explode(';', $filtros);

        foreach($filtros as $key => $filtro){

            $c = explode(':', $filtro); // nome:like:%hb%
            $this->model = $this->model->where($c[0],$c[1],$c[2]); //campo, operador, valor

        }
    }

    public function selectAtributos($atributos){
        $this->model = $this->model->selectRaw($atributos);

    }

    public function getResultado(){

        return $this->model->get();
    }

}

?>