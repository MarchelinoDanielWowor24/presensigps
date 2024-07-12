<?php

namespace App\Http\Controllers;

use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class Unit_KerjaController extends Controller
{
    public function index(Request $request)
    {
        $nama_unker = $request->nama_unker;
        $query = UnitKerja::query();
        $query->select('*');
        if (!empty($nama_unker)) {
            $query->where('nama_unker', 'like', '%' . $nama_unker . '%');
        }
        $unitkerja = $query->get();
        //$unitkerja = DB::table('unit_kerja')->orderBy('kode_unker')->get();
        return view('unitkerja.index', compact('unitkerja'));
    }

    public function store(Request $request)
    {
        $kode_unker = $request->kode_unker;
        $nama_unker = $request->nama_unker;
        $data = [
            'kode_unker' => $kode_unker,
            'nama_unker' => $nama_unker
        ];
        $cek = DB::table('unit_kerja')->where('kode_unker', $kode_unker)->count();
        if ($cek > 0) {
            return Redirect::back()->with(['Warning' => 'Data Dengan Kode Unit Kerja. ' . $kode_unker . ' Sudah Ada']);
        }
        $simpan = DB::table('unit_kerja')->insert($data);
        if ($simpan) {
            return Redirect::back()->with(['success' => 'Data Berhasil Disimpan']);
        } else {
            return Redirect::back()->with(['warning' => 'Data Gagal Disimpan']);
        }
    }

    public function edit(Request $request)
    {
        $kode_unker = $request->kode_unker;
        $unitkerja = DB::table('unit_kerja')->where('kode_unker', $kode_unker)->first();
        return view('unitkerja.edit', compact('unitkerja'));
    }

    public function update($kode_unker, Request $request)
    {
        $nama_unker = $request->nama_unker;
        $data = [
            'nama_unker' => $nama_unker
        ];
        $update = DB::table('unit_kerja')->where('kode_unker', $kode_unker)->update($data);
        if ($update) {
            return Redirect::back()->with(['success' => 'Data Berhasil Diupdate']);
        } else {
            return Redirect::back()->with(['warning' => 'Data Gagal Diupdate']);
        }
    }

    public function delete($kode_unker)
    {
        $hapus = DB::table('unit_kerja')->where('kode_unker', $kode_unker)->delete();
        if ($hapus) {
            return Redirect::back()->with(['success' => 'Data Berhasil Dihapus']);
        } else {
            return Redirect::back()->with(['warning' => 'Data Gagal Dihapus']);
        }
    }
}
