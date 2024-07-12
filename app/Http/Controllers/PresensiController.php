<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class PresensiController extends Controller
{
    public function gethari()
    {
        $hari = date("D");
        switch ($hari) {
            case 'Sun';
                $hari_ini = 'Minggu';
                break;
            case 'Mon';
                $hari_ini = 'Senin';
                break;
            case 'Tue';
                $hari_ini = 'Selasa';
                break;
            case 'Wed';
                $hari_ini = 'Rabu';
                break;
            case 'Thu';
                $hari_ini = 'Kamis';
                break;
            case 'Fri';
                $hari_ini = 'Jumat';
                break;
            case 'Sat';
                $hari_ini = 'Sabtu';
                break;
            default;
                $hari_ini = "Tidak Diketahui";
                break;
        }
        return $hari_ini;
    }

    public function create()
    {
        $hariini = date('Y-m-d');            //query ini berfungsi untuk mengecek apakah pegawai sudah melakukan absen hari ini
        $namahari = $this->gethari();
        //dd($namahari);
        $nik = Auth::guard('pegawai')->user()->nik;
        $cek = DB::table('absensi')->where('tgl_presensi', $hariini)->where('nik', $nik)->count();
        $lok_kantor = DB::table('konfigurasi_lokasi')->where('id', 1)->first();
        $jamkerja = DB::table('konfigurasi_jam_kerja')
            ->join('jam_kerja', 'konfigurasi_jam_kerja.kode_jam_kerja', '=', 'jam_kerja.kode_jam_kerja')
            ->where('nik', $nik)->where('hari', $namahari)->first();
        //dd($jamkerja);
        return view('presensi.create', compact('cek', 'lok_kantor', 'jamkerja'));
    }
    public function store(Request $request)
    {
        $nik = Auth::guard('pegawai')->user()->nik;
        $tgl_presensi = date("Y-m-d");
        $jam = date("H:i:s");
        $lok_kantor = DB::table('konfigurasi_lokasi')->where('id', 1)->first();
        $lok = explode(",", $lok_kantor->lokasi_kantor);
        $latitudekantor = $lok[0];
        $longitudekantor = $lok[1];
        $lokasi = $request->lokasi;
        $lokasiuser = explode(",", $lokasi);
        $latitudeuser = $lokasiuser[0];
        $longitudeuser = $lokasiuser[1];
        $jarak = $this->distance($latitudekantor, $longitudekantor, $latitudeuser, $longitudeuser);
        $radius = round($jarak["meters"]);
        $namahari = $this->gethari();
        $jamkerja = DB::table('konfigurasi_jam_kerja')
            ->join('jam_kerja', 'konfigurasi_jam_kerja.kode_jam_kerja', '=', 'jam_kerja.kode_jam_kerja')
            ->where('nik', $nik)->where('hari', $namahari)->first();

        $cek = DB::table('absensi')->where('tgl_presensi', $tgl_presensi)->where('nik', $nik)->count();

        if ($cek > 0) {
            $ket = "out";
        } else {
            $ket = "in";
        }
        $image = $request->image;
        $folderPath = "public/uploads/presensi/";
        $FormatName = $nik . "-" . $tgl_presensi . "-" . $ket;
        $image_parts = explode(";base64", $image);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = $FormatName . ".png";
        $file = $folderPath . $fileName;
        //


        if ($radius > $lok_kantor->radius) {
            echo "error|Maaf anda berada di luar Radius, Jarak Anda " . $radius . " Meter dari Kantor|radius";
        } else {
            if ($cek > 0) {
                if ($jam < $jamkerja->jam_pulang) {
                    echo "error|Maaf Belum Waktunya Pulang|out";
                } else {
                    $data_pulang = [
                        'jam_out' => $jam,
                        'foto_out' => $fileName,
                        'lokasi_out' => $lokasi
                    ];
                    $update = DB::table('absensi')->where('tgl_presensi', $tgl_presensi)->where('nik', $nik)->update($data_pulang);
                    if ($update) {
                        echo "success|Terima kasih, Selamat Beristirahat|out";
                        Storage::put($file, $image_base64);
                    } else {
                        echo "error|Maaf Gagal Absen|out";
                    }
                }
            } else { //jika belum absen maka insert data absen masuknya
                if ($jam < $jamkerja->awal_jam_masuk) {
                    echo "error|Maaf Belum Waktunya Melakukan Presensi|in";
                } else if ($jam > $jamkerja->akhir_jam_masuk) {
                    echo "error|Terlambat, Karena Wajtu Presensi Sudah Habis|in";
                } else {
                    $data = [
                        'nik' => $nik,
                        'tgl_presensi' => $tgl_presensi,
                        'jam_in' => $jam,
                        'foto_in' => $fileName,
                        'lokasi_in' => $lokasi
                    ];
                    $simpan = DB::table('absensi')->insert($data);
                    if ($simpan) {
                        echo "success|Absensi masuk berhasil|in";
                        Storage::put($file, $image_base64);
                    } else {
                        echo "error|Maaf Gagal Absen|in";
                    }
                }
            }
        }
    }
    //Menghitung Jarak(radius)
    function distance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        $feet = $miles * 5280;
        $yards = $feet / 3;
        $kilometers = $miles * 1.609344;
        $meters = $kilometers * 1000;
        return compact('meters');
    }

    public function editprofile()
    {
        $nik = Auth::guard('pegawai')->user()->nik;
        $pegawai = DB::table('pegawai')->where('nik', $nik)->first();
        return view('presensi.editprofile', compact('pegawai'));
    }

    public function updateprofile(Request $request)
    {
        $nik = Auth::guard('pegawai')->user()->nik;
        $nama_lengkap = $request->nama_lengkap;
        //$no_hp = $request->no_hp;
        $password = Hash::make($request->password);
        $pegawai = DB::table('pegawai')->where('nik', $nik)->first();
        if ($request->hasFile('foto')) {
            $foto = $nik . "." . $request->file('foto')->getClientOriginalExtension();
        } else {
            $foto = $pegawai->foto;
        }

        if (empty($request->password)) {           //jika password kosong(tidak diubah) maka nama dan no hp saja yang diubah
            $data = [
                'nama_lengkap' => $nama_lengkap,
                //'no_hp' => $no_hp,
                'foto' => $foto
            ];
        } else {
            $data = [                   //semua data yang diubah
                'nama_lengkap' => $nama_lengkap,
                //'no_hp' => $no_hp,
                'password' => $password,
                'foto' => $foto

            ];
        }
        $update = DB::table('pegawai')->where('nik', $nik)->update($data);
        if ($update) {
            if ($request->hasFile('foto')) {
                $folderPath = "public/uploads/pegawai/";
                $request->file('foto')->storeAs($folderPath, $foto);
            }
            return Redirect::back()->with(['success' => 'Data Berhasil Diupdate']);
        } else {
            return Redirect::back()->with(['error' => 'Data Gagal Diupdate']);
        }
    }

    public function histori()
    {
        $namabulan = [
            "", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September",
            "Oktober", "November", "Desember"
        ];
        return view('presensi.histori', compact('namabulan'));
    }

    public function gethistori(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $nik = Auth::guard('pegawai')->user()->nik;

        $histori = DB::table('absensi')
            ->whereRaw('MONTH(tgl_presensi)="' . $bulan . '"')
            ->whereRaw('YEAR(tgl_presensi)="' . $tahun . '"')
            ->where('nik', $nik)
            ->orderBy('tgl_presensi')
            ->get();

        return view('presensi.gethistori', compact('histori'));
    }

    public function monitoring()
    {
        return view('presensi.monitoring');
    }

    public function getpresensi(Request $request)
    {
        $tanggal = $request->tanggal;
        $presensi = DB::table('absensi')
            ->select('absensi.*', 'nama_lengkap', 'nama_unker')
            ->join('pegawai', 'absensi.nik', '=', 'pegawai.nik')
            ->join('unit_kerja', 'pegawai.kode_unker', '=', 'unit_kerja.kode_unker')
            ->where('tgl_presensi', $tanggal)
            ->get();
        return view('presensi.getpresensi', compact('presensi'));
    }

    public function tampilkanpeta(Request $request)
    {
        $id = $request->id;
        $presensi = DB::table('absensi')->where('id', $id)
            ->join('pegawai', 'absensi.nik', '=', 'pegawai.nik')
            ->first();
        return view('presensi.showmap', compact('presensi'));
    }

    public function laporan()
    {
        $namabulan = [
            "", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September",
            "Oktober", "November", "Desember"
        ];
        $pegawai = DB::table('pegawai')->orderBy('nama_lengkap')->get();
        return view('presensi.laporan', compact('namabulan', 'pegawai'));
    }

    public function cetaklaporan(Request $request)
    {
        $nik = $request->nik;
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $namabulan = [
            "", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September",
            "Oktober", "November", "Desember"
        ];
        $pegawai = DB::table('pegawai')->where('nik', $nik)
            ->join('unit_kerja', 'pegawai.kode_unker', '=', 'unit_kerja.kode_unker')
            ->first();
        $presensi = DB::table('absensi')
            ->where('nik', $nik)
            ->whereRaw('MONTH(tgl_presensi)="' . $bulan . '"')
            ->whereRaw('YEAR(tgl_presensi)="' . $tahun . '"')
            ->orderBy('tgl_presensi')
            ->get();
        if (isset($_POST['exportexcel'])) {
            $time = date("d-m-Y H:i:s");
            //
            header("Content-type: application/vnd-ms-excel");
            //
            header("Content-Disposition: attachment; filename=Rekap Absensi Pegawai $time.csv");
        }
        return view('presensi.cetaklaporan', compact('bulan', 'tahun', 'namabulan', 'pegawai', 'presensi'));
    }

    public function rekap()
    {
        $namabulan = [
            "", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September",
            "Oktober", "November", "Desember"
        ];
        return view('presensi.rekap', compact('namabulan'));
    }

    public function cetakrekap(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $namabulan = [
            "", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September",
            "Oktober", "November", "Desember"
        ];
        $rekap = DB::table('absensi')
            ->selectRaw('absensi.nik,nama_lengkap,
    MAX(IF(DAY(tgl_presensi) = 1,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_1,
    MAX(IF(DAY(tgl_presensi) = 2,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_2,
    MAX(IF(DAY(tgl_presensi) = 3,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_3,
    MAX(IF(DAY(tgl_presensi) = 4,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_4,
    MAX(IF(DAY(tgl_presensi) = 5,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_5,
    MAX(IF(DAY(tgl_presensi) = 6,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_6,
    MAX(IF(DAY(tgl_presensi) = 7,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_7,
    MAX(IF(DAY(tgl_presensi) = 8,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_8,
    MAX(IF(DAY(tgl_presensi) = 9,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_9,
    MAX(IF(DAY(tgl_presensi) = 10,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_10,
    MAX(IF(DAY(tgl_presensi) = 11,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_11,
    MAX(IF(DAY(tgl_presensi) = 12,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_12,
    MAX(IF(DAY(tgl_presensi) = 13,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_13,
    MAX(IF(DAY(tgl_presensi) = 14,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_14,
    MAX(IF(DAY(tgl_presensi) = 15,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_15,
    MAX(IF(DAY(tgl_presensi) = 16,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_16,
    MAX(IF(DAY(tgl_presensi) = 17,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_17,
    MAX(IF(DAY(tgl_presensi) = 18,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_18,
    MAX(IF(DAY(tgl_presensi) = 19,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_19,
    MAX(IF(DAY(tgl_presensi) = 20,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_20,
    MAX(IF(DAY(tgl_presensi) = 21,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_21,
    MAX(IF(DAY(tgl_presensi) = 22,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_22,
    MAX(IF(DAY(tgl_presensi) = 23,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_23,
    MAX(IF(DAY(tgl_presensi) = 24,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_24,
    MAX(IF(DAY(tgl_presensi) = 25,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_25,
    MAX(IF(DAY(tgl_presensi) = 26,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_26,
    MAX(IF(DAY(tgl_presensi) = 27,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_27,
    MAX(IF(DAY(tgl_presensi) = 28,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_28,
    MAX(IF(DAY(tgl_presensi) = 29,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_29,
    MAX(IF(DAY(tgl_presensi) = 30,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_30,
    MAX(IF(DAY(tgl_presensi) = 31,CONCAT(jam_in,"-",IFNULL (jam_out,"00:00:00")),"")) as tgl_31')
            ->join('pegawai', 'absensi.nik', '=', 'pegawai.nik')
            ->whereRaw('MONTH(tgl_presensi)="' . $bulan . '"')
            ->whereRaw('YEAR(tgl_presensi)="' . $tahun . '"')
            ->groupByRaw('absensi.nik,nama_lengkap')
            ->get();

        if (isset($_POST['exportexcel'])) {
            $time = date("d-m-Y H:i:s");
            //
            header("Content-type: application/vnd-ms-excel");
            //
            header("Content-Disposition: attachment; filename=Rekap Absensi $time.xls");
        }
        return view('presensi.cetakrekap', compact('bulan', 'namabulan', 'tahun', 'rekap'));
    }
}
