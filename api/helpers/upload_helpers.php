<?php
function saveCompressedPhotoKunjungan(array $file, string $noRekening): array
{
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload foto gagal.'];
    }

    // --- Deteksi MIME dengan beberapa fallback ---
    $mime = '';
    $tmp  = $file['tmp_name'];

    if (function_exists('mime_content_type')) {
        $mime = @mime_content_type($tmp) ?: '';
    }

    if (!$mime && function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $mime = finfo_file($finfo, $tmp) ?: '';
            finfo_close($finfo);
        }
    }

    if (!$mime) {
        // getimagesize mengembalikan array dengan indeks 'mime' jika berhasil
        $info = @getimagesize($tmp);
        if (is_array($info) && !empty($info['mime'])) {
            $mime = $info['mime'];
        }
    }

    // terakhir: fallback berdasarkan ekstensi nama file (bila semua deteksi gagal)
    if (!$mime) {
        $extGuess = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (in_array($extGuess, ['png'], true)) {
            $mime = 'image/png';
        } elseif (in_array($extGuess, ['jpg','jpeg'], true)) {
            $mime = 'image/jpeg';
        } else {
            $mime = ''; // tetap kosong kalau bener2 nggak ketahuan
        }
    }

    $allowed = ['image/jpeg', 'image/jpg', 'image/png'];
    if ($mime && !in_array(strtolower($mime), $allowed, true)) {
        return ['success' => false, 'message' => 'Format foto harus JPG/PNG.'];
    }

    $targetDir = __DIR__ . '/../img/kunjungan';
    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            return ['success' => false, 'message' => 'Folder upload tidak bisa dibuat.'];
        }
    }

    $ext   = strtolower(pathinfo($file['name'] ?? 'jpg', PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png'], true)) {
        // fallback bila nama file aneh
        $ext = ($mime === 'image/png') ? 'png' : 'jpg';
    }

    $norek = preg_replace('~[^0-9A-Za-z]~', '', (string)$noRekening);
    $date  = date('Ymd_His');
    $rand  = substr(bin2hex(random_bytes(2)), 0, 4);
    $name  = "KUNJUNGAN-{$norek}-{$date}-{$rand}.{$ext}";
    $dest  = "$targetDir/$name";

    // Kalau GD tidak tersedia, langsung simpan apa adanya (tanpa kompresi)
    $gdAvailable = function_exists('imagecreatetruecolor') &&
                   (function_exists('imagecreatefromjpeg') || function_exists('imagecreatefrompng'));

    if (!$gdAvailable) {
        if (!move_uploaded_file($tmp, $dest)) {
            return ['success' => false, 'message' => 'Gagal memindahkan file upload.'];
        }
        return [
            'success'   => true,
            'message'   => 'Foto tersimpan (tanpa kompresi, GD tidak aktif).',
            'file_name' => $name,
            'file_path' => $dest,
            'file_url'  => "img/kunjungan/$name",
            'file_size' => round(filesize($dest) / 1024, 1) . ' KB',
        ];
    }

    // === Kompresi dengan GD ===
    $size = @getimagesize($tmp);
    if (!$size) {
        return ['success' => false, 'message' => 'Gagal membaca ukuran gambar.'];
    }
    [$w, $h] = [$size[0], $size[1]];
    $maxBytes = 1024 * 1024; // 1MB
    $maxSide  = 2000;

    // open
    if ($ext === 'png') {
        $img = @imagecreatefrompng($tmp);
        if (!$img) return ['success' => false, 'message' => 'Gagal membaca PNG.'];
        imagealphablending($img, true);
        imagesavealpha($img, true);
    } else {
        $img = @imagecreatefromjpeg($tmp);
        if (!$img) return ['success' => false, 'message' => 'Gagal membaca JPEG.'];
    }

    // resize jika terlalu besar
    if ($w > $maxSide || $h > $maxSide) {
        $scale = min($maxSide / $w, $maxSide / $h);
        $nw = max(400, (int)($w * $scale));
        $nh = max(400, (int)($h * $scale));
        $tmpImg = imagecreatetruecolor($nw, $nh);
        // preserve transparency kalau PNG
        if ($ext === 'png') {
            imagealphablending($tmpImg, false);
            imagesavealpha($tmpImg, true);
        }
        imagecopyresampled($tmpImg, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img);
        $img = $tmpImg;
    }

    if ($ext === 'png') {
        for ($lvl = 7; $lvl <= 9; $lvl++) {
            imagepng($img, $dest, $lvl);
            if (filesize($dest) <= $maxBytes) break;
        }
    } else {
        for ($q = 85; $q >= 60; $q -= 5) {
            imagejpeg($img, $dest, $q);
            if (filesize($dest) <= $maxBytes) break;
        }
    }
    imagedestroy($img);

    return [
        'success'   => true,
        'message'   => 'Foto tersimpan.',
        'file_name' => $name,
        'file_path' => $dest,
        'file_url'  => "img/kunjungan/$name",
        'file_size' => round(filesize($dest) / 1024, 1) . ' KB',
    ];
}
