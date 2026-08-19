<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Jadwal Imsakiyah Ramadhan {{ $year }} H - {{ $city }}</title>
    <style>
        @page {
            margin: 12mm 10mm 10mm 10mm;
            size: a4 portrait;
        }
        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 8.5px;
            line-height: 1.2;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #047857;
            padding-bottom: 8px;
            margin-bottom: 10px;
            position: relative;
        }
        .header-logo {
            position: absolute;
            left: 5px;
            top: 2px;
            max-height: 50px;
            max-width: 60px;
        }
        .header-title {
            font-size: 16px;
            font-weight: bold;
            color: #047857;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .header-subtitle {
            font-size: 9px;
            color: #475569;
            margin: 2px 0 0 0;
        }
        .header-meta {
            font-size: 8px;
            color: #64748b;
            margin: 2px 0 0 0;
        }
        .title-box {
            text-align: center;
            margin-bottom: 8px;
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 4px;
            padding: 5px;
        }
        .main-title {
            font-size: 12px;
            font-weight: bold;
            color: #065f46;
            margin: 0;
            text-transform: uppercase;
        }
        .location-info {
            font-size: 8.5px;
            color: #047857;
            margin-top: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 3.5px 4px;
            text-align: center;
        }
        th {
            background-color: #047857;
            color: #ffffff;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .highlight-buka {
            background-color: #fef3c7 !important;
            font-weight: bold;
            color: #92400e;
        }
        .highlight-imsak {
            background-color: #e0f2fe !important;
            font-weight: bold;
            color: #0369a1;
        }
        .phase-1 {
            border-left: 3px solid #10b981;
        }
        .phase-2 {
            border-left: 3px solid #0d9488;
        }
        .phase-3 {
            border-left: 3px solid #f59e0b;
        }
        .doa-container {
            width: 100%;
            margin-top: 6px;
            margin-bottom: 8px;
        }
        .doa-box {
            width: 48.5%;
            float: left;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 5px;
            background-color: #f8fafc;
            box-sizing: border-box;
            font-size: 7.5px;
        }
        .doa-box.right {
            float: right;
        }
        .doa-title {
            font-weight: bold;
            color: #065f46;
            font-size: 8.5px;
            margin-bottom: 3px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 2px;
        }
        .doa-arabic {
            font-size: 9px;
            direction: rtl;
            text-align: right;
            margin: 2px 0;
            color: #0f172a;
            line-height: 1.3;
        }
        .doa-latin {
            font-style: italic;
            color: #334155;
            margin-bottom: 2px;
        }
        .doa-trans {
            color: #64748b;
        }
        .clearfix {
            clear: both;
        }
        .footer-section {
            margin-top: 10px;
            width: 100%;
        }
        .notes-box {
            width: 58%;
            float: left;
            font-size: 7.5px;
            color: #64748b;
            line-height: 1.3;
        }
        .sig-box {
            width: 38%;
            float: right;
            text-align: center;
            font-size: 8px;
        }
        .doc-footer {
            margin-top: 8px;
            font-size: 7px;
            color: #94a3b8;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 3px;
        }
    </style>
</head>
<body>

    <!-- Header / Kop Masjid -->
    <div class="header">
        @if(isset($settings['logo_path']) && $settings['logo_path'] && file_exists(public_path($settings['logo_path'])))
            <img class="header-logo" src="{{ public_path($settings['logo_path']) }}" alt="Logo">
        @endif
        <h1 class="header-title">{{ $settings['site_name'] ?? 'Masjid Al-Hidayah' }}</h1>
        <p class="header-subtitle">{{ $settings['address'] ?? 'Jl. Masjid No. 1, Pusat Kota' }}</p>
        <p class="header-meta">
            @if(isset($settings['phone']) && $settings['phone']) Telp: {{ $settings['phone'] }} @endif
            @if(isset($settings['email']) && $settings['email']) | Email: {{ $settings['email'] }} @endif
        </p>
    </div>

    <!-- Title Banner -->
    <div class="title-box">
        <h2 class="main-title">Jadwal Imsakiyah Ramadhan {{ $year }} H / 2025 M</h2>
        <div class="location-info">
            Wilayah: <b>{{ $city }}</b> &bull; Koordinat: {{ round($lat, 4) }}&deg;, {{ round($lng, 4) }}&deg; &bull; Hisab Kemenag RI (+2 mnt Ihtiyat)
        </div>
    </div>

    <!-- Main Table -->
    <table>
        <thead>
            <tr>
                <th width="5%">Ramadhan</th>
                <th width="19%">Hari, Tanggal</th>
                <th width="9%" style="background-color: #0284c7;">Imsak</th>
                <th width="9%">Subuh</th>
                <th width="9%">Terbit</th>
                <th width="9%">Dhuha</th>
                <th width="9%">Dzuhur</th>
                <th width="9%">Ashar</th>
                <th width="11%" style="background-color: #d97706;">Maghrib (Buka)</th>
                <th width="11%">Isya</th>
            </tr>
        </thead>
        <tbody>
            @foreach($scheduleData['schedule'] as $row)
            <tr class="phase-{{ $row['day'] <= 10 ? 1 : ($row['day'] <= 20 ? 2 : 3) }}">
                <td style="font-weight: bold; background-color: #f1f5f9;">{{ $row['day'] }}</td>
                <td style="text-align: left; padding-left: 5px;">{{ $row['formatted_date'] }}</td>
                <td class="highlight-imsak">{{ $row['imsak'] }}</td>
                <td>{{ $row['subuh'] }}</td>
                <td style="color: #64748b;">{{ $row['terbit'] }}</td>
                <td style="color: #64748b;">{{ $row['dhuha'] }}</td>
                <td>{{ $row['dzuhur'] }}</td>
                <td>{{ $row['ashar'] }}</td>
                <td class="highlight-buka">{{ $row['maghrib'] }}</td>
                <td>{{ $row['isya'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Doa Panduan -->
    <div class="doa-container">
        <div class="doa-box">
            <div class="doa-title">Niat Puasa Ramadhan</div>
            <div class="doa-arabic">نَوَيْتُ صَوْمَ غَدٍ عَنْ أَدَاءِ فَرْضِ شَهْرِ رَمَضَانَ هَذِهِ السَّنَةِ لِلَّهِ تَعَالَى</div>
            <div class="doa-latin">"Nawaitu shauma ghadin 'an adaa'i fardhi syahri ramadhaana hadzihis sanati lillaahi ta'aalaa"</div>
            <div class="doa-trans">Artinya: "Saya niat berpuasa esok hari untuk menunaikan fardhu bulan Ramadhan tahun ini karena Allah Ta'ala."</div>
        </div>

        <div class="doa-box right">
            <div class="doa-title">Doa Berbuka Puasa</div>
            <div class="doa-arabic">ذَهَبَ الظَّمَأُ وَابْتَلَّتِ الْعُرُوقُ وَثَبَتَ الأَجْرُ إِنْ شَاءَ اللَّهُ</div>
            <div class="doa-latin">"Dzahabaz zhama'u wabtallatil 'uruuqu wa tsabatal ajru in syaa-allaah"</div>
            <div class="doa-trans">Artinya: "Telah hilang rasa haus, telah basah urat-urat, dan telah pasti pahala, insya Allah." (HR. Abu Dawud)</div>
        </div>
        <div class="clearfix"></div>
    </div>

    <!-- Signatures & Notes -->
    <div class="footer-section">
        <div class="notes-box">
            <b>Catatan Penting:</b><br>
            1. Waktu Imsak ditetapkan 10 menit sebelum adzan Subuh sebagai tanda kehati-hatian (ihtiyat).<br>
            2. Penetapan awal 1 Ramadhan & 1 Syawal 1446 H tetap menunggu hasil Sidang Isbat Pemerintah RI.<br>
            3. Jadwal dihisab berdasarkan koordinat resmi lokasi masjid dengan standar Kementerian Agama RI.
        </div>

        <div class="sig-box">
            {{ $city }}, 1 Ramadhan {{ $year }} H<br>
            <b>Ketua Dewan Kemakmuran Masjid</b>
            <br><br><br>
            <b><u>{{ $settings['chairman_name'] ?? 'Pengurus DKM' }}</u></b>
        </div>
        <div class="clearfix"></div>
    </div>

    <div class="doc-footer">
        Dicetak otomatis oleh Sistem Informasi Masjid (PiMasjid) pada {{ $generated_at }} WIB &bull; Website: {{ url('/') }}
    </div>

</body>
</html>
