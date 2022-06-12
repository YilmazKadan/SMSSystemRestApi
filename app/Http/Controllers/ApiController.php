<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use OpenApi\Annotations as OA;



class ApiController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/kayit",
     * tags={"Base API"},
     * summary="Üye Kaydı",
     * description="Sisteme yeni kayıt açma",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="application/x-www-form-urlencoded",
     *            @OA\Schema(
     *               type="object",
     *               required={"name","email", "password"},
     *               @OA\Property(property="name", type="text"),
     *               @OA\Property(property="email", type="mail"),
     *               @OA\Property(property="password", type="text"),
     *            ),
     *        ),
     *    ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Register Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function kayit(Request $request)
    {
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


    /**
     * @OA\Post(
     * path="/api/giris",
     * tags={"Base API"},
     * summary="Üye girişi",
     * description="Buradan üye girişi yapılır ve JWT token oluşturulur",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="application/x-www-form-urlencoded",
     *            @OA\Schema(
     *               type="object",
     *               required={"email", "password"},
     *               @OA\Property(property="email", type="mail"),
     *               @OA\Property(property="password", type="text"),
     *            ),
     *        ),
     *    ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Giriş başarılı",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
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
            if (!$token = JWTAuth::attempt($credentials)) {
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

        //Giriş yapıldıktan sonra geriye oluşturulan tokeni dönüyoruz.
        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }

    /**
     * @OA\Post(
     * path="/api/cikis",
     * tags={"Base API"},
     * summary="Üye çıkışı",
     * security={{"bearerAuth":{}}},
     * description="Buradan üye çıkışı yapılır ve JWT token silinir",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Çıkış başarılı",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Token Bulunamadı"),
     * )
     */
    public function cikis(Request $request)
    {
        try {
            JWTAuth::parseToken()->invalidate(true);

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
     * /**
     * @OA\Get(
     * path="/api/profil",
     * tags={"Base API"},
     * summary="Profil",
     * description="Bu istek aktif olarak oturum açmış user'ın bilgilerini döndürür.",
     * security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Başarılı",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Token Bulunamadı"),
     * )
     *
     *
     * Bu metot aktif olarak tokeni bulunan kullanıcıyı döndürür
     *
     * @param Request $request
     * @return void
     */
    public function profil(Request $request)
    {
        $user = $this->user();
        return response()->json(['user' => $user]);
    }
}
