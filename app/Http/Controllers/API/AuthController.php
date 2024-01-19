<?php

namespace App\Http\Controllers\API;
use App\Models\User;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     tags={"Auth"},
     *     path="/api/register",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"email", "surname", "name", "phone", "image", "password", "password_confirmation"},
     *                 @OA\Property(
     *                     property="image",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="surname",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="phone",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password_confirmation",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Register new user.")
     * )
     */
    public function register(Request $request){
        $input = $request->all();
        $validation = Validator::make($input,[
            'name'=> 'required|string',
            'surname'=> 'required|string',
            'image'=> 'required|string',
            'phone'=> 'required|string',
            'email'=> 'required|email',
            'password'=> 'required|string|min:6',
        ]);

        if($validation->fails()) {
            return response()->json($validation->errors(), Response::HTTP_BAD_REQUEST);
        }

        $imageName = uniqid().".jpg";
        $sizes = [50,150,300,600,1200];
        $manager = new ImageManager(new Driver());
        foreach ($sizes as $size) {
            $imageSave = $manager->read($input["image"]);
            $imageSave->scale(width: $size);
            $path = public_path("upload_users/".$size."_".$imageName);
            $imageSave->toJpg()->save($path);
        }
        $user = User::create(array_merge(
            $validation->validated(),
            ['password'=>bcrypt($input['password']), 'image'=>$imageName]
        ));
        return response()->json(['user'=>$user], Response::HTTP_OK);
    }
    /**
     * @OA\Post(
     *     tags={"Auth"},
     *     path="/api/login",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"email","password"},
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Login.")
     * )
     */
    public function login(Request $request){
        $findUser = User::all();
        foreach ($findUser as $user){
            if($user['email']==$request['email']){
               if(password_verify($request['password'],$user['password'])){
                   $secret_key="nazarsecretkey";
                   $payload = array(
                       "user_id" => $user['id'],
                       "username" => $user['email'],
                       "exp" => time() + 3600,

                   );
                   $jwt = JWT::encode($payload, $secret_key, 'HS256');
                   return response()->json(['your token'=>$jwt],Response::HTTP_OK)
                       ->cookie('cookies_jwt_token',$jwt,60);
               }
            }
        }


        return response()->json("Invalid password or login",Response::HTTP_UNAUTHORIZED);
    }
    /**
     * @OA\Get(
     *     tags={"Auth"},
     *     path="/api/leave",
     *     @OA\Response(response="200", description="Leave.")
     * )
     */
    public function leave(){

            return response()->json("Bye",Response::HTTP_OK)
                ->cookie('cookies_jwt_token',null,1);

    }

}
