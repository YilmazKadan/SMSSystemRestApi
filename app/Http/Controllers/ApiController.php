<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiController extends Controller
{
    public function kayit(Request $request){
        //Kullanıcı verilerinin kontrolü
        $data = $request->only('name', 'email', 'password');
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Veriler eğer uygun değil ise hata cevabı veriyoruz
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 200);
        }

        //İsterler karşılanmış ise kayıt gerçekleşiyor
        $user = User::create([
        	'name' => $request->name,
        	'email' => $request->email,
        	'password' => bcrypt($request->password)
        ]);

        //Kullanıcı oluşturulduktan sonra , başarılı cevabını veriyoruz.
        return response()->json([
            'success' => true,
            'message' => 'Kullanıcı başarılı bir şekilde oluşturuldu',
            'data' => $user
        ], Response::HTTP_OK);
    }

    public function giris(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Kontroller sağlanıyor
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Hatalı verileri 200'response code'u ile döndürüyoruz.
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is validated
        //Crean token
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                	'success' => false,
                	'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
    	return $credentials;
            return response()->json([
                	'success' => false,
                	'message' => 'Could not create token.',
                ], 500);
        }

 		//Token created, return with success response and jwt token
        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }

    public function cikis(Request $request)
    {
        //token varlığı kontrolü
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Token eksikliği kontrolü
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

		//İstekteki parametereler uygunsa çıkışı gerçekleşiyor
        try {
            JWTAuth::invalidate($request->token);

            return response()->json([
                'success' => true,
                'message' => 'Çıkış yapıldı'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Çıkış yapılırken bir sorun oluştu'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Bu metot aktif olarak tokeni bulunan kullanıcıyı döndürür
     *
     * @param Request $request
     * @return void
     */
    public function profil(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        $user = JWTAuth::authenticate($request->token);

        return response()->json(['user' => $user]);
    }
}

