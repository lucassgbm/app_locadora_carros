<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use App\Repositories\MarcaRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MarcaController extends Controller
{
    public function __construct(Marca $marca){
        $this->marca = $marca;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $marcaRepository = new MarcaRepository($this->marca);

        if($request->has('atributos_modelos')){

            $atributos_modelos = 'modelos:id,'.$request->atributos_modelos;
            $marcaRepository->selectAtributosRegistrosRelacionados($atributos_modelos);
            
        }else {

            $marcaRepository->selectAtributosRegistrosRelacionados('modelos');

        }

        if($request->has('filtro')){

            $marcaRepository->filtro($request->filtro);

        }

        if($request->has('atributos')){

            $marcaRepository->selectAtributos($request->atributos);

        }

        // $marcas = Marca::all();
        // $marcas = $this->marca->with('modelos')->get();
        return response()->json($marcaRepository->getResultado(), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate($this->marca->rules(), $this->marca->feedback());

        // $marca = Marca::create($request->all());

        // arquivo config/filesystems.php
        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens/marcas', 'public');

        $marca = $this->marca->create([
            'nome' => $request->nome,
            'imagem' => $imagem_urn,

        ]);
        return response()->json($marca, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Integer  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $marca = $this->marca->with('modelos')->find($id);
        if($marca === null){
            return response()->json(['erro' => 'Recurso pesquisado não existe'], 404);
        }
        return response()->json($marca, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Marca  $marca
     * @return \Illuminate\Http\Response
     */
    public function edit(Marca $marca)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Integer  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $marca = $this->marca->find($id);

        if($marca === null){
            return response()->json(['erro' => 'O recurso solicitado não existe'],404);
        }

        if($request->method() === 'PATCH'){

            $regrasDinamicas = array();

            $teste = '';

            #percorrendo todas as regras definidas no Model
            foreach ($marca->rules() as $input => $regra){

                #colocar apenas as regras aplicáveis aos parâmetros parciais da requisição PATCH

                if(array_key_exists($input, $request->all())){
                    $regrasDinamicas[$input] = $regra;
                }

            }
                
            $request->validate($regrasDinamicas, $marca->feedback());

        // método PUT
        }else {
            $request->validate($marca->rules(), $marca->feedback());
        }

        // remove o arquivo antigo caso tenho sido enviado um arquivo novo
        if($request->file('imagem')){
            Storage::disk('public')->delete($marca->imagem);
        }

        // arquivo config/filesystems.php
        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens/marcas', 'public');

        //preencher o objeto $marca com os dados do request
        $marca->fill($request->all());
        $marca->imagem = $imagem_urn;

        $marca->save();

        // $marca->update([
            
        //     'nome' => $request->nome,
        //     'imagem' => $imagem_urn,
        // ]);

        return response()->json($marca, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Marca  $marca
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $marca = $this->marca->find($id);

        if($marca === null){
            return response()->json(['erro' => 'O recurso solicitado não existe'],404);
        }

        // remove o arquivo
        Storage::disk('public')->delete($marca->imagem);

        $marca->delete();
        return response()->json(['msg' => 'A marca foi removida com sucesso'], 200);
    }
}
