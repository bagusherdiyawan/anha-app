<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AlamatController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $alamats = Alamat::where('user_id', $request->user()->id)->get();
        return $this->successResponse(
            $alamats,
            'Daftar alamat berhasil diambil.'
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:50',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'district' => 'nullable|string|max:100',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'notes' => 'nullable|string|max:255',
            'default' => 'nullable|boolean',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Format data tidak sesuai',
                400,
                $validator->errors()
            );
        }

        if ($request->boolean('default')) {
            Alamat::where('user_id', $request->user()->id)->update(['default' => null]);
        }

        try {
            $alamat = Alamat::create([
                'user_id' => $request->user()->id,
                'label' => $request->label,
                'recipient_name' => $request->recipient_name,
                'phone' => $request->phone,
                'address' => $request->address,
                'district' => $request->district,
                'city' => $request->city,
                'province' => $request->province,
                'postal_code' => $request->postal_code,
                'notes' => $request->notes,
                'default' => $request->boolean('default') ? 1 : 0,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            if ($alamat) {
                return $this->successResponse(
                    $alamat,
                    'Alamat berhasil ditambahkan',
                    201
                );
            } else {
                return $this->errorResponse(
                    'Alamat gagal ditambahkan',
                    500
                );
            }
        } catch (Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat menghubungkan ke server',
                500
            );
        }
    }

    public function show(Request $request, $id)
    {
        $alamat = Alamat::where('user_id', $request->user()->id)->find($id);
        if (!$alamat) {
            return $this->errorResponse(
                'Detail alamat tidak ditemukan',
                404
            );
        }

        return $this->successResponse(
            $alamat,
            'Alamat berhasil ditemukan'
        );
    }

    public function update(Request $request, $id)
    {
        $alamat = Alamat::where('user_id', $request->user()->id)->find($id);
        if (!$alamat) {
            return $this->errorResponse(
                'Detail alamat gagal ditemukan',
                404
            );
        }

        $validator = Validator::make($request->all(), [
            'label' => 'nullable|string|max:50',
            'recipient_name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'district' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string|max:255',
            'default' => 'nullable|boolean',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Format data tidak sesuai',
                400,
                $validator->errors()
            );
        }

        if ($request->has('default') && $request->boolean('default')) {
            Alamat::where('user_id', $request->user()->id)->update(['default' => null]);
        }

        $update = $alamat->update($request->only([
            'label',
            'recipient_name',
            'phone',
            'address',
            'district',
            'city',
            'province',
            'postal_code',
            'notes',
            'default',
            'latitude',
            'longitude'
        ]));

        try {
            if ($update) {
                return $this->successResponse(
                    [$alamat->fresh()],
                    'Alamat berhasil diubah'
                );
            } else {
                return $this->errorResponse(
                    'Alamat gagal diubah',
                    404
                );
            }
        } catch (Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat menghubungkan ke server',
                500
            );
        }
    }

    public function destroy(Request $request, $id)
    {
        $alamat = Alamat::where('user_id', $request->user()->id)->find($id);
        if (!$alamat) {
            return $this->errorResponse(
                'Detail alamat gagal ditemukan',
                404
            );
        }

        try {
            $delete = $alamat->delete();

            if ($delete) {
                return $this->successResponse(
                    null,
                    'Alamat berhasil dihapus'
                );
            } else {
                return $this->errorResponse(
                    'Alamat gagal dihapus',
                    404
                );
            }
        } catch (Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat menghubungkan ke server',
                500
            );
        }
    }

    public function ctrl_setDefaultAlamat(Request $request, $id)
    {
        $alamat = Alamat::where('user_id', $request->user()->id)->find($id);
        if (!$alamat) {
            return $this->errorResponse(
                'Detail alamat gagal ditemukan',
                404
            );
        }

        Alamat::where('user_id', $request->user()->id)
            ->update(['default' => null]);

        $alamat->default = 1;
        $alamat->save();

        return $this->successResponse(
            null,
            'Alamat berhasil diupdate sebagai alamat default' 
        );
    }
}
