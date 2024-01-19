<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\File;
/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Усіх із Різдвом та Новим роком!",
 *      description="Demo my Project ",
 *      @OA\Contact(
 *          email="admin@gmail.com"
 *      ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="https://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 */
class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     tags={"Category"},
     *     path="/api/categories",
     *     @OA\Response(response="200", description="List Categories.")
     * )
     */
    function getAll(Request $request)
    {
        /*$token = $request->cookie("cookies_jwt_token");
        if(!$token){
            return response()->json("You are not auth",401,['Charset'=>'utf-8']);
        }*/
        $list=Categories::all();
        return response()->json($list,200,['Charset'=>'utf-8']);
    }
    /**
     * @OA\Post(
     *     tags={"Category"},
     *     path="/api/categories/create",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","price","image"},
     *                 @OA\Property(
     *                     property="image",
     *                     type="file",
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                      property="price",
     *                      type="number"
     *                  )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Add Category.")
     * )
     */
    function createCategory(Request $request)
    {
        if($request->header('Authorize')=="")return response()->json("You do not authorized",401);
        $input = $request->all();
        $image= $request->file("image");
        $manager = new ImageManager(new Driver());
        $imageName = uniqid().".jpg";
        $sizes = [50,150,300,600,1200];
        foreach ($sizes as $size){
            $imageSave = $manager->read($image);
            $imageSave->scale(width:$size);
            $path = $_SERVER['DOCUMENT_ROOT'] . "/upload/{$size}_{$imageName}";
            $imageSave->toJpeg()->save($path);
        }
        $input["image"]=$imageName;
        $category = Categories::create($input);
        return response()->json($category,200,['Charset'=>'utf-8']);
    }
    /**
     * @OA\Get(
     *     tags={"Category"},
     *     path="/api/categories/{id}",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Ідентифікатор категорії",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(response="200", description="List Categories."),
     * @OA\Response(
     *    response=404,
     *    description="Wrong id",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Sorry, wrong Category Id has been sent. Pls try another one.")
     *        )
     *     )
     * )
     */
    function getById($id){
        $obj = Categories::find($id);
        if($obj)return response()->json($obj,200,['Charset'=>'utf-8']);

        return response()->json("Not found",404,['Charset'=>'utf-8']);

    }
    /**
     * @OA\Post(
     *     tags={"Category"},
     *     path="/api/categories/edit/{id}",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Ідентифікатор категорії",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(
     *                     property="image",
     *                     type="file"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                       @OA\Property(
     *                       property="price",
     *                       type="number"
     *                   )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Add Category.")
     * )
     */
    function edit(Request $request,$id){
        $input = $request->all();

        $category = Categories::find($id);

        if($category === null){return response()->json("Id $id not found",404,['Charset'=>'utf-8']);}
        $input['name'] = ($input['name'] !== null && $input['name'] !== '') ? $input['name'] : $category->name;
        $input['price'] = ($input['price'] !== null && $input['price'] !== '') ? $input['price'] : $category->price;
        $input['image'] = isset($input['image'])&&$input['image']!='' ? $input['image'] : $category->image;
        if($input['image']!=$category->image){
            $image= $request->file("image");
            $manager = new ImageManager(new Driver());
            $imageName = uniqid().".jpg";
            $sizes = [50,150,300,600,1200];
            foreach ($sizes as $size){
                $imageSave = $manager->read($image);
                $imageSave->scale(width:$size);
                $path = $_SERVER['DOCUMENT_ROOT'] . "/upload/{$size}_{$imageName}";
                File::delete($_SERVER['DOCUMENT_ROOT'] . "/upload/{$size}_{$category->image}");
                $imageSave->toJpeg()->save($path);
            }
            $input["image"]=$imageName;

        }
        $category->update($input);
        return response()->json($category,200,['Charset'=>'utf-8']);

    }
    /**
     * @OA\Delete(
     *     path="/api/categories/delete/{id}",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Ідентифікатор категорії",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успішне видалення категорії"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Категорії не знайдено"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Не авторизований"
     *     )
     * )
     */
    function delete($id)
    {
        $category = Categories::find($id);
        if($category === null){return response()->json("Id $id not found",404,['Charset'=>'utf-8']);}
        $sizes = [50,150,300,600,1200];
        foreach ($sizes as $size){
            File::delete($_SERVER['DOCUMENT_ROOT'] . "/upload/{$size}_{$category->image}");
        }
        $category->delete();
    }


}
