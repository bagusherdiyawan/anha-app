<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Payment_detail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    function __construct(){}
    function cekout(Request $request){
        try {
            $limit = $request->query('limit');
            $tawal = $request->query('tglawal');
            $takhir = $request->query('tglakhir');

            if ($limit == 0) {
                $limit = 1000000;
            }

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

            if (!empty($tawal)) {
                if (empty($takhir)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Parameter tanggal akhir tidak boleh kosong',
                    ], 404);   
                }
                $riwayat_pembelian = $riwayat_pembelian
                ->whereDate('payments.created_at','>=',$tawal)
                ->whereDate('payments.created_at','<=',$takhir);
            }


            $riwayat_pembelian = $riwayat_pembelian->orderBy('payments.id','desc');


            if ($riwayat_pembelian->exists()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Riwayat pembelian berhasil ditemukan',
                    'data' => $riwayat_pembelian->paginate($limit)
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
    function gantiStatusPembayaran(Request $request, $id){
        $payment = Payment::find($id);
        if (!$payment){
            return response()->json([
                'success' => false,
                'message' => 'Detail payment gagal ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status_bayar' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak sesuai '.$validator->errors()
            ], 400);
        }

        $update = $payment->update([
            'status_bayar' => $request->status_bayar,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        try {
            if ($update) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status pembayaran berhasil diubah',
                ]);
            }
            else
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Status pembayaran gagal diubah',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungkan ke server',
            ], 500);
        }
    }
    function totalCekout($tawal,$takhir,$status){
        $totalCekout = 0;
        $riwayat_pembelian = Payment::select('total')
        ->join('users as us','us.id','=','payments.user_id')
        ->join('alamats as al','al.id','=','payments.alamat_id')
        ->join('payment_statuss as ps','ps.id','=','payments.status_bayar')
        ->join('rekening_tokos as rt','rt.id','=','payments.rektoko_id');

        if (!empty($tawal)) {
            if (empty($takhir)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tanggal akhir tidak boleh kosong',
                ], 404);   
            }
            $riwayat_pembelian = $riwayat_pembelian
            ->whereDate('payments.created_at','>=',$tawal)
            ->whereDate('payments.created_at','<=',$takhir);
        }

        if (!empty($status)) {
            $riwayat_pembelian = $riwayat_pembelian
            ->where('status_bayar',$status);                
        }

        $riwayat_pembelian = $riwayat_pembelian->get();

        foreach ($riwayat_pembelian as $key) {
            $totalCekout = $totalCekout + (Float)$key->total;
        }

        return $totalCekout;
    }
    function index(Request $request){
        try {
            $limit = $request->query('limit');
            $tawal = $request->query('tglawal');
            $takhir = $request->query('tglakhir');
            $status = $request->query('status');

            if ($limit == 0) {
                $limit = 1000000;
            }

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
            ->join('rekening_tokos as rt','rt.id','=','payments.rektoko_id');

            if (!empty($tawal)) {
                if (empty($takhir)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Parameter tanggal akhir tidak boleh kosong',
                    ], 404);   
                }
                $riwayat_pembelian = $riwayat_pembelian
                ->whereDate('payments.created_at','>=',$tawal)
                ->whereDate('payments.created_at','<=',$takhir);
            }

            if (!empty($status)) {
                $riwayat_pembelian = $riwayat_pembelian
                ->where('status_bayar',$status);                
            }

            $riwayat_pembelian = $riwayat_pembelian->orderBy('payments.id','desc');


            if ($riwayat_pembelian->exists()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Riwayat pembelian berhasil ditemukan',
                    'data' => [
                        'total_sum' => $this->totalCekout($tawal,$takhir,$status),
                        'laporan' => $riwayat_pembelian->paginate($limit),
                    ]
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
                    'bank_foto' => url('public/public').'/'.$payment->bank_foto,
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
}