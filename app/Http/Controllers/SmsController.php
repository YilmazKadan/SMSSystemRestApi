<?php

namespace App\Http\Controllers;

use App\Models\Sms;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

class SmsController extends Controller
{

    /**
     *
     *  * @OA\Get(
     *     tags={"SMS API"},
     *     path="/api/sms",
     *     summary="SMS Raporlarını Listeler",
     *     security={{"bearerAuth":{}}},
     *  * @OA\Parameter(
     *    description="Tarih aralığının başlangıcı",
     *    in="query",
     *    name="from_date",
     *    required=false,
     *    @OA\Schema(
     *       type="string",
     * format ="date-time",
     *    )
     * ),
     *  * @OA\Parameter(
     *    description="Tarih aralığının sonu",
     *    in="query",
     *    name="to_date",
     *    required=false,
     *    @OA\Schema(
     *       type="string",
     * format ="date-time",
     *    )
     * ),
     *     @OA\Response(response="401", description="fail"),
     *     @OA\Response(response="404", description="fail"),
     *     @OA\Response(response="200", description="success")
     * )
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Tarih filtresini oluşturuyoruz, eğer girilmez ise,
        // bugün ve 100 yıl sonrasını aralık olarak belirliyoruz
        $fromDate = $request->input('from_date')  ?? Carbon::today();
        $toDate   = $request->input('to_date')   ?? Carbon::today()->addYears(100);

        $smses = $this->user()->sms()->whereBetween('created_at', [$fromDate, $toDate]);


        return  response()->json([
            "data" => $smses->get()
        ]);
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
     * @OA\Post(
     * path="/api/sms",
     * tags={"SMS API"},
     * summary="SMS kaydı",
     * security={{"bearerAuth":{}}},
     * description="Buradan sms gönderimi ve kaydı gerçekleşir",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *            @OA\Schema(
     *               type="object",
     *               required={"title","number", "message"},
     *               @OA\Property(property="title", type="text"),
     *               @OA\Property(property="number", type="tel"),
     *               @OA\Property(property="message", type="text"),
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
     *
     *
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->only('title', 'number', 'message');
        // Türk telefonu için regex kullanıldı
        $validator = Validator::make($data, [
            'title' => 'required|string',
            'number' => 'required|regex:/^(05)([0-9]{2})\s?([0-9]{3})\s?([0-9]{2})\s?([0-9]{2})$/',
            'message' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        $sms = $this->user()->sms()->create([
            'title' => $request->title,
            'number' => $request->number,
            'message' => $request->message
        ]);

        // Bu kısımda SMS servisi aracılığı ile SMS gönderimi sağlanabilir.
        // SMS api hizmeti burada kuyruğa alınıyor, Case'de tam olarak 500 istek ile ne kastedildiğini anlayamadım.
        \App\Jobs\SmsSendJob::dispatch($sms);

        // Sms başarılı bir şekilde eklenildikten sonra
        return response()->json([
            'success' => true,
            'message' => 'SMS başarılı bir şekilde gönderildi',
            'data' => $sms
        ], Response::HTTP_OK);
    }



    /**
     * @OA\Get(
     *     tags={"SMS API"},
     *     path="/api/sms/{id}",
     *     summary="SMS rapor detayını getirir",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *        name="id",
     *        in="path",
     *        description="SMS id",
     *        @OA\Schema(
     *           type="integer",
     *           format="int64"
     *        ),
     *        required=true,
     *        example=1
     *     ),
     *     @OA\Response(response="401", description="fail"),
     *     @OA\Response(response="404", description="fail"),
     *     @OA\Response(response="200", description="success")
     * )
     *
     * Display the specified resource.
     *
     * @param  \App\Models\Sms  $sms
     * @return \Illuminate\Http\Response
     */
    public function show(Sms $sms)
    {
        $userId = $this->user()->id;
        // SMS sahibi oturum açan kullanıcı mı ?
        if ($sms->user_id != $userId)
            return response()->json(['error' => "Erişmeye çalıştığınız mesajın sahibi siz değilsiniz"], 200);

        return response()->json([
            'success' => true,
            'data' => $sms
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sms  $sms
     * @return \Illuminate\Http\Response
     */
    public function edit(Sms $sms)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sms  $sms
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sms $sms)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sms  $sms
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sms $sms)
    {
        //
    }
}
