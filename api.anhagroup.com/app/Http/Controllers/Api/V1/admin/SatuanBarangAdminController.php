<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SatuanBarangResource;
use App\Models\BarangSatuan;
use Illuminate\Http\Request;

class SatuanBarangAdminController extends Controller
{
    /**
     * GET /api/admin/satuan-barang
     */
    public function index(Request $request)
    {
        $search = $request->q ?? $request->search;
        $perPage = $request->input('per_page', $request->input('limit', 10));
        if ($perPage === 'all') {
            $perPage = 1000;
        }

        $query = BarangSatuan::query();

        if ($search) {
            $query->where('nama', 'like', "%{$search}%");
        }

        $data = $query->orderBy('nama')->paginate((int) $perPage);

        return SatuanBarangResource::collection($data);
    }

    /**
     * POST /api/admin/satuan-barang
     */
    public function store(Request $request)
    {
    	$request->validate([
    		'nama' => 'required|max:255|unique:barangs_satuan,nama',
    	]);

    	$satuan = BarangSatuan::create([
    		'nama' => $request->nama,
    	]);

    	return response()->json([
    		'success' => true,
    		'message' => 'Satuan barang berhasil ditambahkan.',
    		'data' => new SatuanBarangResource($satuan)
    	], 201);
    }

    /**
     * GET /api/admin/satuan-barang/{id}
     */
    public function show($id)
    {
    	$data = BarangSatuan::find($id);
    	if (!$data) {
    		return response()->json([
    			'success' => false,
    			'message' => 'Data tidak ditemukan.'
    		], 404);
    	}

    	$satuan = BarangSatuan::findOrFail($id);

    	return new SatuanBarangResource($satuan);
    }

    /**
     * PUT /api/admin/satuan-barang/{id}
     */
    public function update(Request $request, $id)
    {
    	$satuan = BarangSatuan::find($id);
    	
    	if (!$satuan) {
    		return response()->json([
    			'success' => false,
    			'message' => 'Data tidak ditemukan.'
    		], 404);
    	}

    	$request->validate([
    		'nama' => 'required|max:255|unique:barangs_satuan,nama,' . $id,
    	]);

    	$satuan->update([
    		'nama' => $request->nama,
    	]);

    	return response()->json([
    		'success' => true,
    		'message' => 'Satuan barang berhasil diupdate.',
    		'data' => new SatuanBarangResource($satuan)
    	]);
    }

    /**
     * DELETE /api/admin/satuan-barang/{id}
     */
    public function destroy($id)
    {
    	$satuan = BarangSatuan::find($id);

    	if (!$satuan) {
    		return response()->json([
    			'success' => false,
    			'message' => 'Data tidak ditemukan.'
    		], 404);
    	}

    	$satuan->delete();

    	return response()->json([
    		'success' => true,
    		'message' => 'Satuan barang berhasil dihapus.'
    	]);
    }
}
