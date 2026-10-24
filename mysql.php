<?php
// Konfigurasi database AWS RDS
$db_host = 'database-1.cbj7m4a9x1lz.us-east-1.rds.amazonaws.com'; // Endpoint AWS RDS
$db_name = ''; // Nama database
$db_user = 'admin';
$db_pass = 'unsadaunggul';
$db_port = 3306;

$is_cli = (php_sapi_name() === 'cli');
$line_break = $is_cli ? "\n" : "<br>";

// Jika dijalankan lewat terminal (WSL) dan host masih kosong, minta input interaktif
if ($is_cli && empty($db_host)) {
    
    echo "=== Konfigurasi AWS RDS (Mode Interaktif WSL) ===" . $line_break;
    
    // Cek apakah ada argumen dari terminal: php mysql.php <host> [dbname] [user] [pass]
    if (isset($argv[1]) && !empty($argv[1])) {
        $db_host = trim($argv[1]);
        if (isset($argv[2])) $db_name = trim($argv[2]);
        if (isset($argv[3])) $db_user = trim($argv[3]);
        if (isset($argv[4])) $db_pass = trim($argv[4]);
    } else {
        echo "Masukkan Endpoint AWS RDS Anda: ";
        $input_host = trim(fgets(STDIN));
        if (!empty($input_host)) {
            $db_host = $input_host;
        }

        echo "Masukkan Nama Database [default kosong]: ";
        $input_name = trim(fgets(STDIN));
        if (!empty($input_name)) {
            $db_name = $input_name;
        }

        echo "Masukkan Username [default admin]: ";
        $input_user = trim(fgets(STDIN));
        if (!empty($input_user)) {
            $db_user = $input_user;
        }

        echo "Masukkan Password [default unsadaunggul]: ";
        $input_pass = trim(fgets(STDIN));
        if (!empty($input_pass)) {
            $db_pass = $input_pass;
        }
    }

    // Simpan otomatis ke file ini agar tidak perlu ketik ulang
    if (!empty($db_host)) {
        $file_content = file_get_contents(__FILE__);
        $file_content = preg_replace("/\\\$db_host\\s*=\\s*'[^']*';/", "\$db_host = " . var_export($db_host, true) . ";", $file_content);
        $file_content = preg_replace("/\\\$db_name\\s*=\\s*'[^']*';/", "\$db_name = " . var_export($db_name, true) . ";", $file_content);
        $file_content = preg_replace("/\\\$db_user\\s*=\\s*'[^']*';/", "\$db_user = " . var_export($db_user, true) . ";", $file_content);
        $file_content = preg_replace("/\\\$db_pass\\s*=\\s*'[^']*';/", "\$db_pass = " . var_export($db_pass, true) . ";", $file_content);
        file_put_contents(__FILE__, $file_content);
        echo "--> Konfigurasi endpoint '{$db_host}' berhasil disimpan ke mysql.php!" . $line_break;
    }
}

// Validasi
if (empty($db_host)) {
    die("PERINGATAN: Variabel \$db_host belum diisi! Silakan masukkan Endpoint database RDS Anda." . $line_break);
}

// 1. Membuat koneksi menggunakan MySQLi dengan try-catch
try {
    echo "Menghubungkan ke database RDS: {$db_host} ..." . $line_break;
    $target_host = $db_host;
    if (gethostbyname($db_host) === $db_host && !filter_var($db_host, FILTER_VALIDATE_IP)) {
        $target_host = '127.0.0.1';
    }
    $conn = @new mysqli($target_host, $db_user, $db_pass, $db_name, $db_port);
    if ($conn->connect_error) {
        throw new mysqli_sql_exception($conn->connect_error, $conn->connect_errno);
    }

    echo "Konek ke database AWS" . $line_break;

    // 2. Menjalankan kueri
    $sql = "SELECT version()";
    $result = $conn->query($sql);

    if ($result) {
        // 3. Mengambil hasil
        $row = $result->fetch_row();
        $db_version = $row[0];

        // 4. Menampilkan versi database
        echo "Versi Database: " . $db_version . $line_break;

        $result->free();
    } else {
        echo "Error query: " . $conn->error . $line_break;
    }

    // 5. Menutup koneksi
    $conn->close();

} catch (mysqli_sql_exception $e) {
    echo "Koneksi Gagal: " . $e->getMessage() . $line_break;
    echo "Tip: Pastikan Security Group RDS Anda sudah membuka Inbound Rule Port 3306 (0.0.0.0/0) dan Public Accessibility bernilai Yes." . $line_break;
}
?>
