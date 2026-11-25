<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Payment_detail;
use App\Models\Rekening_toko;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Kreait\Firebase\Factory;

class PaymentFrontController extends Controller{
    function __construct(){
        $this->tglNow = date('Y-m-d');
    }
    function index(){
        return response()->json([
            'status' => false,
            'message' => 'Fitur tidak tersedia',
        ], 404);
    }
    function store(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'alamat_id' => 'required|exists:alamats,id',
            'biaya_antar_id' => 'required|exists:biaya_antars,id',
            'barang_id' => 'required|array',
            'barang_qty' => 'required|array',
            'barang_subtotal' => 'required|array',
            'total' => 'required',
            'rektoko_id' => 'required|exists:rekening_tokos,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak sesuai '.$validator->errors()
            ], 400);
        }

        $dataInsert = [
            'user_id' => $request->user_id, 
            'alamat_id' => $request->alamat_id,
            'biaya_antar_id' => $request->biaya_antar_id,
            'total' => $request->total,
            'rektoko_id' => $request->rektoko_id,
        ];

        $createStatus = Rekening_toko::select('type')->where('id',$request->rektoko_id)->first();
        if ($createStatus->type == 'ewallet') {
            $dataInsert['status_bayar'] = 4; 
        } else{
            $dataInsert['status_bayar'] = 2;
        }



        $payment = Payment::create($dataInsert);

        try {
            if ($payment) {

                $dtInsPayBar = [];
                foreach ($request->barang_id as $key => $value) {
                    $dt = [
                        'payment_id' => $payment->id,
                        'barang_id' => $value,
                        'qty' => (!empty($request->barang_qty[$key])) ? $request->barang_qty[$key] : 0,
                        'subtotal' => (!empty($request->barang_subtotal[$key])) ? $request->barang_subtotal[$key] : 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                    $dtInsPayBar[] = $dt;
                }

                $payment_details = DB::table('payment_details')->insert($dtInsPayBar);

                $dataUser = User::select('name')->where('id',$request->user_id)->first();
                $this->kirimNotifikasi($dataUser->name);

                return response()->json([
                    'success' => true,
                    'message' => 'payment berhasil ditambahkan',
                    'data' => $payment
                ], 201);
            }
            else
            {
                return response()->json([
                    'success' => false,
                    'message' => 'payment gagal ditambahkan',
                ], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungkan ke server',
            ], 500);
        }
    }
    function riwayatPembelian($user_id=null){
        if (empty($user_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Data riwayat pembelian tidak ditemukan',
            ], 404);
        }
        
        try {
            $riwayat_pembelian = Payment::select('payments.id',DB::raw("DATE_FORMAT(payments.created_at,'%Y-%m-%d %H:%i:%s') as tanggal"),'total','ps.nama as status')
            ->join('payment_statuss as ps','ps.id','=','payments.status_bayar')
            ->where('user_id',$user_id)->orderBy('payments.id','desc');

            if ($riwayat_pembelian->exists()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Riwayat pembelian berhasil ditemukan',
                    'data' => $riwayat_pembelian->paginate(10),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Riwayat pembelian gagal ditemukan',
                ], 404);
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan menghubungkan ke server',
            ], 500);
        }
    }
    function riwayatPembelianDetail($payment_id=null){
        if (empty($payment_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Data detail riwayat pembelian tidak ditemukan',
            ], 404);
        }

        try {
            $riwayat_pembelian_detail = Payment_detail::
            select('nama_barang','qty','subtotal')
            ->join('barangs as b','b.id','=','payment_details.barang_id')
            ->where('payment_details.payment_id',$payment_id)->orderBy('payment_details.id','desc');

            $payment = Payment::
            select(
                'rt.nama as bank_nama',
                'rt.nomor as bank_nomor',
                'rt.type as bank_type',
                'rt.foto as bank_foto',
                'rt.keterangan as bank_keterangan',
                'al.alamat as alamat',
                'ps.nama as status'
            )
            ->join('rekening_tokos as rt','rt.id','=','payments.rektoko_id')
            ->join('alamats as al','al.id','=','payments.alamat_id')
            ->join('payment_statuss as ps','ps.id','=','payments.status_bayar')
            ->where('payments.id',$payment_id)->first();

            if ($riwayat_pembelian_detail->exists()) {
                $result = [];

                $result['products'] = $riwayat_pembelian_detail->get();
                $result['payment_method'] = [
                    'bank_nama' => $payment->bank_nama,
                    'bank_nomor' => $payment->bank_nomor,
                    'bank_type' => $payment->bank_type,
                    'bank_foto' => ($payment->bank_foto) ? url('public/public/' . $payment->bank_foto) : null,
                    'bank_keterangan' => $payment->bank_keterangan,
                ];
                $result['address'] = $payment->alamat;
                $result['status'] = $payment->status;

                return response()->json([
                    'success' => true,
                    'message' => 'Riwayat pembelian detail berhasil ditemukan',
                    'data' => $result,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Riwayat pembelian detail gagal ditemukan',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan menghubungkan ke server',
            ], 500);
        }
    }
    function gantiMethodPembayaran(Request $request, $id){
        $payment = Payment::find($id);
        if (!$payment){
            return response()->json([
                'success' => false,
                'message' => 'Detail payment gagal ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'rekening_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak sesuai '.$validator->errors()
            ], 400);
        }

        $update = $payment->update([
            'rektoko_id' => $request->rekening_id,
        ]);

        try {
            if ($update) {
                return response()->json([
                    'success' => true,
                    'message' => 'Methode pembayaran berhasil diubah',
                    'data' => Rekening_toko::find($request->rekening_id),
                ]);
            }
            else
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Methode pembayaran gagal diubah',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungkan ke server',
            ], 500);
        }
    }
    function uploadBuktiTransfer(Request $request, $id){
        $payment = Payment::find($id);
        if (!$payment){
            return response()->json([
                'success' => false,
                'message' => 'Detail payment gagal ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(),[
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak sesuai '.$validator->errors()
            ], 400);
        }

        $foto_root = substr($payment->bukti_bayar, strpos($payment->bukti_bayar, 'photos_buktibayar'));
        $fotoPath = $foto_root;

        if ($request->hasFile('foto')) {
            if ($foto_root && Storage::disk('public')->exists($foto_root)) {
                Storage::disk('public')->delete($foto_root);
            }
            $fotoPath = $request->file('foto')->store('photos_buktibayar', 'public');
        } elseif ($request->input('clear_foto') == 'ya') {
            if ($foto_root && Storage::disk('public')->exists($foto_root)) {
                Storage::disk('public')->delete($foto_root);
            }
            $fotoPath = null;
        }

        $update = $payment->update([
            'bukti_bayar' => $fotoPath,
            'status_bayar' => 4,
        ]);

        try {
            if ($update) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bukti pembayaran berhasil diupload',
                    'data' => payment::find($id),
                ]);
            }
            else
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Bukti pembayaran gagal diupload',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungkan ke server',
            ], 500);
        }
    }
    function getBearer(){
        // Path ke file kunci akun layanan Anda.
        $serviceAccountKeyFile = './storage/app/private/kopma-akprind-firebase-adminsdk-fbsvc-896a00b94c.json';

        // Periksa apakah file kunci ada.
        if (!file_exists($serviceAccountKeyFile)) {
            throw new \Exception('Service account key file not found: ' . $serviceAccountKeyFile);
        }

        // Tentukan scope yang diperlukan untuk FCM.
        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

        // Buat instance kredensial akun layanan.
        $credentials = new ServiceAccountCredentials($scopes, $serviceAccountKeyFile);

        // Dapatkan token otorisasi.
        $accessToken = $credentials->fetchAuthToken()['access_token'];

        return $accessToken;
    }
    function kirimNotifikasi($name){
        $token_bearer = $this->getBearer();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://fcm.googleapis.com/v1/projects/kopma-akprind/messages:send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "message":{
                    "token":"eYVs28rNTdeTczzn7EyoOY:APA91bG08NxkWcBGIINq-stmRSeSSJKN7jfNR1x8EZHYtKVu08slI-VxmNpvyI3Rdd-FJ1W1Na7WAEtOvXr5QODVDQ7e5oOns2uIZ3pW0XBIwqy9lG3sN0o",
                    "notification":{
                        "body":"Ada pesanan dari ",
                        "title":"KOPMA AKPRIND"
                    }
                }
            }',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer '.$token_bearer
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }






    // =============================================
    // FOR APK ADMIN
    // =============================================

    function riwayatPembelianByAdmin(){
        try {
            $riwayat_pembelian = Payment::select(
                'payments.id',
                'us.name as nama_user',
                'al.alamat as nama_alamat',
                'payments.created_at as tanggal',
                'total',
                'ps.nama as status',
                'rt.nama as nama_rekening'
            )
            ->join('users as us','us.id','=','payments.user_id')
            ->join('alamats as al','al.id','=','payments.alamat_id')
            ->join('payment_statuss as ps','ps.id','=','payments.status_bayar')
            ->join('rekening_tokos as rt','rt.id','=','payments.rektoko_id')
            ->where('status_bayar','<>',1);                            



            $riwayat_pembelian = $riwayat_pembelian->orderBy('payments.id','desc');


            if ($riwayat_pembelian->exists()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Riwayat pembelian berhasil ditemukan',
                    'data' => $riwayat_pembelian->paginate(10)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Riwayat pembelian gagal ditemukan',
                ], 404);
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan menghubungkan ke server',
            ], 500);
        }
    }

}