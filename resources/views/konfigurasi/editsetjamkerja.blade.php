@extends('layouts.admin.tabler')
@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">

                    <!-- Page pre-title -->


                    <h2 class="page-title">
                        Update Jam Kerja
                    </h2>
                </div>

            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-12">
                    <table class="table">
                        <tr>
                            <th>NIP</th>
                            <td>{{ $pegawai->nik }}</td>
                        </tr>
                        <tr>
                            <th>Nama Pegawai</th>
                            <td>{{ $pegawai->nama_lengkap }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <form action="/konfigurasi/updatesetjamkerja" method="POST">
                        @csrf
                        <input type="hidden" name="nik" value="{{ $pegawai->nik }}">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Hari</th>
                                    <th>Jam Kerja</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($setjamkerja as $s)
                                    <tr>
                                        <td>{{ $s->hari }}
                                            <input type="hidden" name="hari[]" value="{{ $s->hari }}">
                                        </td>
                                        <td>
                                            <select name="kode_jam_kerja[]" id="kode_jam_kerja" class="form-select">
                                                <option value="">Pilih jam Kerja</option>
                                                @foreach ($jamkerja as $d)
                                                    <option {{ $d->kode_jam_kerja == $s->kode_jam_kerja ? 'selected' : '' }}
                                                        value="{{ $d->kode_jam_kerja }}">
                                                        {{ $d->nama_jam_kerja }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                    </form>

                    </table>
                    <button class="btn btn-success w-100" type="submit">Update</button>
                </div>
                <div class="col-6">
                    <table class="table">
                        <thead>
                            <tr>
                                <th colspan="6">Master Jam Kerja</th>
                            </tr>
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Awal Jam Masuk</th>
                                <th>Jam Masuk</th>
                                <th>Akhir Jam Masuk</th>
                                <th>Jam pulang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($jamkerja as $d)
                                <tr>
                                    <td>{{ $d->kode_jam_kerja }}</td>
                                    <td>{{ $d->nama_jam_kerja }}</td>
                                    <td>{{ $d->awal_jam_masuk }}</td>
                                    <td>{{ $d->jam_masuk }}</td>
                                    <td>{{ $d->akhir_jam_masuk }}</td>
                                    <td>{{ $d->jam_pulang }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
