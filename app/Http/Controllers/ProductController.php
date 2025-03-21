<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MongoDB\Client;
use MongoDB\BSON\ObjectId;

class ProductController extends Controller
{
    protected $db;
    protected $collection;
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->db = new Client(env('MONGO_URI'));
        $this->collection = $this->db->inventory->products;
    }
    public function index()
    {
        try{
            $products = $this->collection->find()->toArray();
            return response()->json($products,Response::HTTP_OK);
        }catch(Exception $ex){
            return response()->json(['error' => $ex->getMessage()],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $valData = $request->validate([
            'name'=>'required|string|max:100',
            'description'=>'required|string|max:1000',
            'price'=>'required|numeric|min:0',
            'category'=>'required|string|max:100',
            'available'=>'required|boolean',
            'ingredients'=>'required|array',
            'quantity'=>'required|integer',
        ]);
        try{
            $exists = $this->collection->findOne(['name'=>$valData['name']]);

            if($exists){
                return response()->json(['error'=>'Ya existe el producto con ese nombre'],Response::HTTP_CONFLICT);
            }

            $data = [
                'name'=> $valData['name'],
                'description'=> $valData['description'],
                'price'=> $valData['price'],
                'category'=> $valData['category'],
                'available'=> $valData['available'],
                'ingredients'=> $valData['ingredients'],
                'quantity'=> $valData['quantity']
            ];
            $product = $this->collection->insertOne($data);
            $data['_id'] = $product->getInsertedId();
            return response()->json([
                'message' => 'Guardado con éxito',
                'product' => $data
            ],Response::HTTP_CREATED);
        }catch(Exception $ex){
            return response()->json(['error' => $ex->getMessage()],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            $product = $this->collection->findOne(['_id'=> new ObjectId($id)]);
            if(!$product){
                return response()->json(['error' => 'No encontrado'],Response::HTTP_NOT_FOUND);
            }
            return response()->json([
                'message' => 'Producto encontrado',
                'producto'=> $product
            ],Response::HTTP_OK);
        }catch(Exception $ex){
            return response()->json(['error' => $ex->getMessage()],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $valData = $request->validate([
            'name'=>'required|string|max:100',
            'description'=>'required|string|max:1000',
            'price'=>'required|numeric|min:0',
            'category'=>'required|string|max:100',
            'available'=>'required|boolean',
            'ingredients'=>'required|array',
            'quantity'=>'required|integer',
        ]);
        try{


            
            $product = $this->collection->updateOne(
                ['_id' => new ObjectId($id)],
                ['$set' => $valData]
            );

            if($product->getMatchedCount() === 0){
                return response()->json(['error' => 'No encontrado'],Response::HTTP_NOT_FOUND);
            }
            
            return response()->json([
                'message' => 'Actualizado con éxito'
            ],Response::HTTP_OK);
        }catch(Exception $ex){
            return response()->json(['error' => $ex->getMessage()],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $product = $this->collection->deleteOne(['_id'=> new ObjectId($id)]);
            if($product->getDeletedCount() === 0){
                return response()->json(['error' => 'No encontrado'],Response::HTTP_NOT_FOUND);
            }
            return response()->json([
                'message' => 'Eliminado con éxito',
                'producto'=> $product
            ],Response::HTTP_OK);
        }catch(Exception $ex){
            return response()->json(['error' => $ex->getMessage()],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function searchByName(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:100',
    ]);

    try {
        $name = $request->input('name');
        $products = $this->collection->find([
            'name' => ['$regex' => '.*' . preg_quote($name, '/') . '.*', '$options' => 'i'] 
        ])->toArray();

        if (empty($products)) {
            return response()->json(['message' => 'No se encontraron productos con ese nombre.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Productos encontrados con éxito',
            'products' => $products
        ], Response::HTTP_OK);
    } catch (Exception $ex) {
        return response()->json(['error' => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

public function low()
{
    try {
        $products = $this->collection->find(
            ['quantity' => ['$lt' => 20]], // Filtro para quantity < 10
            ['sort' => ['quantity' => -1]] // Ordenamiento descendente
        )->toArray();  
        return response()->json($products, Response::HTTP_OK);
    } catch(Exception $ex) {
        return response()->json(
            ['error' => $ex->getMessage()],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}
public function purgar(){
    return response()->json(['Purgado' => 'Ordenes'], Response::HTTP_OK);;
}
}
