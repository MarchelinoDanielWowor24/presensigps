@php
    function selisih($jam_masuk, $jam_keluar)
    {
        [$h, $m, $s] = explode(':', $jam_masuk);
        $dtAwal = mktime($h, $m, $s, '1', '1', '1');
        [$h, $m, $s] = explode(':', $jam_keluar);
        $dtAkhir = mktime($h, $m, $s, '1', '1', '1');
        $dtSelisih = $dtAkhir - $dtAwal;
        $totalmenit = $dtSelisih / 60;
        $jam = explode('.', $totalmenit / 60);
        $sisamenit = $totalmenit / 60 - $jam[0];
        $sisamenit2 = $sisamenit * 60;
        $jml_jam = $jam[0];
        return $jml_jam . ':' . round($sisamenit2);
    }
@endphp
@foreach ($presensi as $d)
    @php
        $foto_in = Storage::url('/uploads/presensi/' . $d->foto_in);
        $foto_out = Storage::url('/uploads/presensi/' . $d->foto_out);
    @endphp
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $d->nik }}</td>
        <td>{{ $d->nama_lengkap }}</td>
        <td>{{ $d->nama_unker }}</td>
        <td>{{ $d->jam_in }}</td>
        <td>
            <img src="{{ url($foto_in) }}" class="avatar" alt="">
        </td>
        <td>{!! $d->jam_out != null ? $d->jam_out : '<span class="badge bg-danger">Belum Absen Pulang</span>' !!}</td>
        <td>
            @if ($d->jam_out != null)
                <img src="{{ url($foto_out) }}" class="avatar" alt="">
            @else
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="icon icon-tabler icons-tabler-outline icon-tabler-photo-off">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M15 8h.01" />
                    <path
                        d="M7 3h11a3 3 0 0 1 3 3v11m-.856 3.099a2.991 2.991 0 0 1 -2.144 .901h-12a3 3 0 0 1 -3 -3v-12c0 -.845 .349 -1.608 .91 -2.153" />
                    <path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" />
                    <path d="M16.33 12.338c.574 -.054 1.155 .166 1.67 .662l3 3" />
                    <path d="M3 3l18 18" />
                </svg>
            @endif
        </td>
        <td>
            @if ($d->jam_in >= '07:00')
                @php
                    $jamterlambat = selisih('07:00:00', $d->jam_in);
                @endphp
                <span class="badge bg-danger">Terlambat {{ $jamterlambat }}</span>
            @else
                <span class="badge bg-success">Tepat Waktu</span>
            @endif
        </td>
        <td>
            <a href="#" class="btn btn-primary tampilkanpeta" id="{{ $d->id }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="icon icon-tabler icons-tabler-outline icon-tabler-map-2">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M12 18.5l-3 -1.5l-6 3v-13l6 -3l6 3l6 -3v7.5" />
                    <path d="M9 4v13" />
                    <path d="M15 7v5.5" />
                    <path
                        d="M21.121 20.121a3 3 0 1 0 -4.242 0c.418 .419 1.125 1.045 2.121 1.879c1.051 -.89 1.759 -1.516 2.121 -1.879z" />
                    <path d="M19 18v.01" />
                </svg>
            </a>
        </td>
    </tr>
@endforeach

<script>
    $(function() {
        $(".tampilkanpeta").click(function(e) {
            var id = $(this).attr("id");
            $.ajax({
                type: 'POST',
                url: '/tampilkanpeta',
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                cache: false,
                success: function(respond) {
                    $("#loadmap").html(respond);
                }
            });
            $("#modal-tampilkanpeta").modal("show");
        });
    });
</script>
