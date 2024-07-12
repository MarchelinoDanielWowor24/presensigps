<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {

        $query = Pegawai::query();
        $query->select('pegawai.*', 'nama_unker');
        $query->join('unit_kerja', 'pegawai.kode_unker', '=', 'unit_kerja.kode_unker');
        $query->orderBy('nama_lengkap');
        if (!empty($request->nama_pegawai)) {
            $query->where('nama_lengkap', 'like', '%' . $request->nama_pegawai . '%');
        }

        if (!empty($request->kode_unker)) {
            $query->where('pegawai.kode_unker', $request->kode_unker);
        }

        $pegawai = $query->paginate(10);

        $unit_kerja = DB::table('unit_kerja')->get();
        return view('pegawai.index', compact('pegawai', 'unit_kerja'));
    }

    public function store(Request $request)
    {
        $nik = $request->nik;
        $nama_lengkap = $request->nama_lengkap;
        $jabatan = $request->jabatan;
        //$no_hp = $request->no_hp;
        $kode_unker = $request->kode_unker;
        $password = Hash::make('1234');
        //$pegawai = DB::table('pegawai')->where('nik', $nik)->first();

        // Pengecekan panjang NIK
        if (strlen($nik) > 18) {
            return Redirect::back()->with(['warning' => 'NIK terlalu panjang, maksimal 18 karakter']);
        }

        if ($request->hasFile('foto')) {
            $foto = $nik . "." . $request->file('foto')->getClientOriginalExtension();
        } else {
            $foto = null;
        }

        try {
            $data = [
                'nik' => $nik,
                'nama_lengkap' => $nama_lengkap,
                //'no_hp' => $no_hp,
                'jabatan' => $jabatan,
                'kode_unker' => $kode_unker,
                'foto' => $foto,
                'password' => $password
            ];
            $simpan = DB::table('pegawai')->insert($data);
            if ($simpan) {
                if ($request->hasFile('foto')) {
                    $folderPath = "public/uploads/pegawai/";
                    $request->file('foto')->storeAs($folderPath, $foto);
                }
                return Redirect::back()->with(['success' => 'Data Berhasil Disimpan']);
            }
        } catch (\Exception $e) {
            //dd($e);
            $message = '';
            if ($e->getCode() == 23000) {
                $message = " Data dengan NIP " . $nik . " Sudah Ada";
            }
            return Redirect::back()->with(['warning' => 'Data Gagal Disimpan' . $message]);
        }
    }

    public function edit(Request $request)
    {
        $nik = $request->nik;
        $unit_kerja = DB::table('unit_kerja')->get();
        $pegawai = DB::table('pegawai')->where('nik', $nik)->first();
        return view('pegawai.edit', compact('unit_kerja', 'pegawai'));
    }

    public function update($nik, Request $request)
    {
        $nik = $request->nik;
        $nama_lengkap = $request->nama_lengkap;
        $jabatan = $request->jabatan;
        //$no_hp = $request->no_hp;
        $kode_unker = $request->kode_unker;
        $password = Hash::make('1234');
        $old_foto = $request->old_foto;
        //$pegawai = DB::table('pegawai')->where('nik', $nik)->first();
        if ($request->hasFile('foto')) {
            $foto = $nik . "." . $request->file('foto')->getClientOriginalExtension();
        } else {
            $foto = $old_foto;
        }

        try {
            $data = [
                'nama_lengkap' => $nama_lengkap,
                //'no_hp' => $no_hp,
                'jabatan' => $jabatan,
                'kode_unker' => $kode_unker,
                'foto' => $foto,
                'password' => $password
            ];
            $update = DB::table('pegawai')->where('nik', $nik)->update($data);
            if ($update) {
                if ($request->hasFile('foto')) {
                    $folderPath = "public/uploads/pegawai/";
                    $folderPathOld = "public/uploads/pegawai/" . $old_foto;
                    Storage::delete($folderPathOld);
                    $request->file('foto')->storeAs($folderPath, $foto);
                }
                return Redirect::back()->with(['success' => 'Data Berhasil Diupdate']);
            }
        } catch (\Exception $e) {
            //dd($e);
            return Redirect::back()->with(['warning' => 'Data Gagal Diupdate']);
        }
    }

    public function delete($nik)
    {
        $delete = DB::table('pegawai')->where('nik', $nik)->delete();
        if ($delete) {
            return Redirect::back()->with(['success' => 'Data Berhasil Dihapus']);
        } else {
            return Redirect::back()->with(['warning' => 'Data Gagal Dihapus']);
        }
    }
}
