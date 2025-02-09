<?php
    $file = "rekapan.txt";
    $harga_vbe = [
        '25K' => [
            'face' => 25000,
            'beli' => 21500
        ],
        '50K' => [
            'face' => 50000,
            'beli' => 45000
        ],
        '100K' => [
            'face' => 100000,
            'beli' => 90000
        ]
    ];

    // Rate jual
    $rate = [
        'IDM' => 0.97,    // Indomaret
        'ALFA' => [
            '25K' => 0.95,
            '50K' => 0.96,
            '100K' => 0.96
        ]
    ];

    if (isset($_GET['clear'])) {
        $selectedPackage = $_GET['package'] ?? 'all';
        
        if (file_exists($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $remaining = [];
            
            foreach ($lines as $line) {
                // Hapus hanya yang sesuai dengan package yang dipilih
                if ($selectedPackage === 'all' || strpos($line, "Voucher: $selectedPackage") !== false) {
                    continue;
                }
                $remaining[] = $line;
            }
            
            // Update file dengan data yang tersisa
            if (empty($remaining)) {
                unlink($file);
            } else {
                file_put_contents($file, implode("\n", $remaining));
            }
        }
        
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit();
    }

    if (isset($_POST['submit'])) {
        $selected_vbes = $_POST['vbe_selection'] ?? [];
        $voucher_codes = array_filter(array_map('trim', explode("\n", $_POST['voucher_code'] ?? '')));
        
        // Validasi pilihan voucher
        if (empty($selected_vbes)) {
            die("Error: Silakan pilih minimal satu jenis voucher");
        }
        
        // Validasi jumlah kode
        if (count($voucher_codes) < 1) {
            die("Error: Silakan masukkan minimal satu kode voucher");
        }
        
        foreach ($selected_vbes as $vbe_name) {
            foreach ($voucher_codes as $code) {
                file_put_contents($file, date("Y-m-d H:i:s") . " | Voucher: " . htmlspecialchars($vbe_name) . " | Kode Voucher: " . htmlspecialchars($code) . "\n", FILE_APPEND);
            }
        }
        
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapan VBE</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #ecf0f1;
            --accent: #3498db;
            --idm-color: #007bff;  /* Biru terang */
            --alfa-color: #dc3545; /* Merah terang */
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f9f9f9;
            margin: 0;
            padding: 20px;
            line-height: 1.5;
            color: #333;
        }

        h2 {
            color: var(--primary);
            font-size: 2rem;
            text-align: center;
            margin: 2rem 0;
            padding-bottom: 0.5rem;
            position: relative;
            font-weight: 600;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--accent);
            border-radius: 2px;
        }

        form, .rekapan-container {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            padding: 12px;
            border: 1px solid #ddd;
            background: #f8f8f8;
            padding-left: 15px;
            transition: all 0.2s;
        }

        .checkbox-label input {
            width: 70px;
            margin-right: 10px;
            padding: 8px;
            border: 1px solid #ccc;
        }

        button {
            background: var(--accent);
            color: white;
            border: none;
            padding: 12px 24px;
            cursor: pointer;
            font-size: 1rem;
        }

        button:hover {
            opacity: 0.9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            background: white;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        table th {
            background: var(--primary);
            color: white;
        }

        .grand-total td {
            background: var(--primary);
            color: white;
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .checkbox-group {
                flex-direction: column;
            }
            
            .checkbox-label {
                width: 100%;
            }
        }

        .voucher-code {
            background: #f8f8f8;
            padding: 8px;
            margin: 4px 0;
            font-family: monospace;
        }

        /* Tambahkan class warna */
        .vbe-idm {
            color: var(--idm-color);
            border-left: 6px solid var(--idm-color);
            background: linear-gradient(90deg, rgba(0,123,255,0.1) 0%, rgba(255,255,255,1) 30%);
        }
        
        .vbe-alfa {
            color: var(--alfa-color);
            border-left: 6px solid var(--alfa-color);
            background: linear-gradient(90deg, rgba(220,53,69,0.1) 0%, rgba(255,255,255,1) 30%);
        }

        /* Update checkbox-label */
        .checkbox-label:hover {
            border-color: currentColor;
        }

        /* Tambahkan di bagian style */
        .hidden {
            display: none;
        }

        /* Perbaiki textarea */
        #voucher_code {
            width: 100%;
            height: 150px;
            margin: 10px 0;
            padding: 10px;
        }
    </style>
    <script>
        function showVoucherInput() {
            document.getElementById('voucher_section').classList.remove('hidden');
        }

        function copyVouchers() {
            let selectedPackage = document.getElementById('copy_selection').value;
            let vouchers = document.querySelectorAll('.voucher-code');
            let voucherText = "";
            let packageMap = new Map();

            // Group vouchers by package
            vouchers.forEach(v => {
                let packageName = v.getAttribute('data-package');
                if (selectedPackage === "all" || packageName === selectedPackage) {
                    if (!packageMap.has(packageName)) {
                        packageMap.set(packageName, []);
                    }
                    packageMap.get(packageName).push(v.innerText);
                }
            });

            // Build text format dengan nomor urut
            packageMap.forEach((codes, packageName) => {
                voucherText += `Kode Voucher ${packageName}:\n`;
                voucherText += codes.map((code, index) => `${index + 1}. ${code}`).join('\n') + '\n\n';
            });

            voucherText = voucherText.trim();

            if (!voucherText) {
                alert("Tidak ada kode voucher untuk Voucher yang dipilih.");
                return;
            }

            navigator.clipboard.writeText(voucherText).then(() => {
                alert(`Kode voucher telah disalin dengan nomor urut!`);
                window.location.href = `?clear=1&package=${encodeURIComponent(selectedPackage)}`;
            }).catch(err => {
                alert("Gagal menyalin, coba secara manual.");
            });
        }

        function updateVoucherSection() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');
            const voucherCodes = document.getElementById('voucher_code').value.split('\n').filter(c => c.trim() !== '');
            
            const voucherSection = document.getElementById('voucher_section');
            if (checkboxes.length > 0) {
                voucherSection.classList.remove('hidden');
            } else {
                voucherSection.classList.add('hidden');
            }
        }

        // Hapus event listener untuk textarea
        document.getElementById('voucher_code').removeEventListener('input', updateVoucherSection);

        // Tambahkan event listener saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            updateVoucherSection(); // Jalankan validasi awal
        });
    </script>
</head>
<body>

    <h2>Rekapan VBE</h2>

    <form action="" method="POST">
        <label>Pilih Jenis Voucher:</label>
        <div class="checkbox-group">
            <!-- VBE IDM -->
            <label class="checkbox-label vbe-idm">
                <input type="checkbox" name="vbe_selection[]" value="VBE IDM 25K" onchange="updateVoucherSection()">
                VBE IDM 25K
            </label>
            <label class="checkbox-label vbe-idm">
                <input type="checkbox" name="vbe_selection[]" value="VBE IDM 50K" onchange="updateVoucherSection()">
                VBE IDM 50K
            </label>
            <label class="checkbox-label vbe-idm">
                <input type="checkbox" name="vbe_selection[]" value="VBE IDM 100K" onchange="updateVoucherSection()">
                VBE IDM 100K
            </label>
            
            <!-- VBE ALFA -->
            <label class="checkbox-label vbe-alfa">
                <input type="checkbox" name="vbe_selection[]" value="VBE ALFA 25K" onchange="updateVoucherSection()">
                VBE ALFA 25K
            </label>
            <label class="checkbox-label vbe-alfa">
                <input type="checkbox" name="vbe_selection[]" value="VBE ALFA 50K" onchange="updateVoucherSection()">
                VBE ALFA 50K
            </label>
            <label class="checkbox-label vbe-alfa">
                <input type="checkbox" name="vbe_selection[]" value="VBE ALFA 100K" onchange="updateVoucherSection()">
                VBE ALFA 100K
            </label>
        </div>

        <div id="voucher_section" class="hidden">
            <label for="voucher_code">Masukkan Kode Voucher (satu per baris):</label>
            <textarea name="voucher_code" id="voucher_code" required placeholder="Contoh: 
ABC123
DEF456"></textarea>
            
            <button type="submit" name="submit">Simpan</button>
        </div>
    </form>

    <?php
    // Tampilkan isi rekapan.txt jika ada
    if (file_exists($file)) {
        echo "<div class='rekapan-container'>";
        echo "<h2>Data Rekapan</h2>";
        
        $file_contents = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $unique_packages = ["all" => "Semua Voucher"];
        $total_keseluruhan = 0;
        $counter = [];

        echo "<table border='1' cellpadding='5' style='margin-bottom: 20px;'>";
        echo "<tr><th>Tipe</th><th>Voucher</th><th>Jumlah</th><th>Total Harga</th><th>Harga Jual</th></tr>";

        foreach ($file_contents as $line) {
            if (preg_match("/Voucher: VBE (ALFA|IDM) (\d+[K])/i", $line, $matches)) {
                $type = trim($matches[1]);
                $jenis = trim($matches[2]);
                $counter[$type][$jenis] = ($counter[$type][$jenis] ?? 0) + 1;
            }
        }

        // Variabel total
        $total_pendapatan = 0;
        
        // Tampilkan per jenis dan tipe
        foreach (['IDM', 'ALFA'] as $type) {
            $type_total = 0;
            $type_pendapatan = 0;
            
            foreach ($harga_vbe as $jenis => $details) {
                $jumlah = $counter[$type][$jenis] ?? 0;
                if($jumlah > 0) {
                    $total = $jumlah * $details['face'];
                    $pendapatan = $total * $rate[$type][$jenis];
                    
                    $type_total += $total;
                    $type_pendapatan += $pendapatan;
                    
                    $total_keseluruhan += $total;
                    $total_pendapatan += $pendapatan;
                    
                    echo "<tr>";
                    echo "<td data-label='Tipe'><span class='vbe-$type'>VBE $type</span></td>";
                    echo "<td data-label='Voucher'>$jenis</td>";
                    echo "<td data-label='Jumlah'>$jumlah x</td>";
                    echo "<td data-label='Total Harga'>Rp " . number_format($total, 0, ',', '.') . "</td>";
                    echo "<td data-label='Harga Jual'>Rp " . number_format($pendapatan, 0, ',', '.') . "</td>";
                    echo "</tr>";
                }
            }
            
            if($type_total > 0) {
                echo "<tr style='background:#f8f8f8;' class='total-".strtolower($type)."'>";
                echo "<td colspan='3'>Total $type</td>";
                echo "<td>Rp " . number_format($type_total, 0, ',', '.') . "</td>";
                echo "<td>Rp " . number_format($type_pendapatan, 0, ',', '.') . "</td>";
                echo "</tr>";
            }
        }

        echo "<tr class='grand-total'>";
        echo "<td colspan='3'>TOTAL KESELURUHAN</td>";
        echo "<td>Rp " . number_format($total_keseluruhan, 0, ',', '.') . "</td>";
        echo "<td style='background:#2ecc71;color:white;'>Rp " . number_format($total_pendapatan, 0, ',', '.') . "</td>";
        echo "</tr>";
        echo "</table>";

        echo "<pre id='voucher_list'>";
        
        $file_contents = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $unique_packages = ["all" => "Semua Voucher"];

        foreach ($file_contents as $line) {
            echo "$line\n";

            if (preg_match("/Voucher: (.*?) \| Kode Voucher: (.*)/", $line, $matches)) {
                $package = trim($matches[1]);
                $voucher = trim($matches[2]);
                $type = strpos($package, 'IDM') !== false ? 'idm' : 'alfa';
                echo "<div class='voucher-code hidden' data-package='$package'><span class='vbe-$type'>$voucher</span></div>";
                $unique_packages[$package] = $package;
            }
        }

        echo "</pre>";

        // Dropdown pilihan Voucher sebelum menyalin
        echo "<label for='copy_selection'>Pilih Voucher untuk Disalin:</label>";
        echo "<select id='copy_selection'>";
        foreach ($unique_packages as $key => $value) {
            echo "<option value='$key'>$value</option>";
        }
        echo "</select>";

        echo "<button class='copy-btn' onclick='copyVouchers()'>Salin Kode Voucher</button>";
        echo "</div>";
    }
    ?>

</body>
</html>
