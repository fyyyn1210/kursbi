# KursBI

[![Latest Version](https://img.shields.io/packagist/v/fyyyn1210/kursbi.svg?style=flat-square)](https://packagist.org/packages/fyyyn1210/kursbi)
[![Total Downloads](https://img.shields.io/packagist/dt/fyyyn1210/kursbi.svg?style=flat-square)](https://packagist.org/packages/fyyyn1210/kursbi)
[![License](https://img.shields.io/packagist/l/fyyyn1210/kursbi.svg?style=flat-square)](https://packagist.org/packages/fyyyn1210/kursbi)

**Paket PHP untuk mengambil data kurs mata uang dari Bank Indonesia (BI) untuk PHP & Laravel.**

## 📋 Deskripsi

KursBI adalah library PHP yang memudahkan pengambilan data kurs mata uang dari API resmi Bank Indonesia. Paket ini mendukung:

- ✅ Pengambilan kurs untuk satu atau beberapa mata uang sekaligus
- ✅ Penentuan rentang tanggal data kurs
- ✅ Pembatasan jumlah data hasil (limit)
- ✅ Konversi jumlah nominal berdasarkan kurs beli dan jual
- ✅ Caching data agar efisien dan mengurangi request berulang
- ✅ User-Agent dinamis untuk request yang lebih natural
- ✅ Support untuk Laravel Cache

## 🚀 Instalasi

Install via Composer:

```bash
composer require fyyyn1210/kursbi
```

## 💡 Penggunaan

### Import Class

```php
use Fyyyn1210\KursBI;
```

### Inisialisasi

```php
$kursBI = new KursBI();
```

## 📖 Contoh Penggunaan

### 1. Mendapatkan Kurs USD Hari Ini

```php
use Fyyyn1210\KursBI;

$kursBI = new KursBI();
$result = $kursBI->getKurs('USD');

print_r($result);
```

**Return Value:**
```php
Array
(
    [success] => 1
    [mata_uang_count] => 1
    [data] => Array
        (
            [0] => Array
                (
                    [mata_uang] => USD
                    [periode] => Array
                        (
                            [start] => 2025-08-02
                            [end] => 2025-08-02
                        )
                    [jumlah_input] => 
                    [jumlah_data] => 1
                    [rata_rata] => Array
                        (
                            [beli] => 15485.50
                            [jual] => 15561.50
                        )
                    [total_konversi] => 
                    [data] => Array
                        (
                            [0] => Array
                                (
                                    [tanggal] => 2025-08-02
                                    [nilai] => 1
                                    [beli] => 15485.50
                                    [jual] => 15561.50
                                )
                        )
                )
        )
)
```

### 2. Mendapatkan Kurs Beberapa Mata Uang

```php
$result = $kursBI->getKurs('USD,EUR,JPY');

print_r($result);
```

**Return Value:**
```php
Array
(
    [success] => 1
    [mata_uang_count] => 3
    [data] => Array
        (
            [0] => Array
                (
                    [mata_uang] => USD
                    [periode] => Array
                        (
                            [start] => 2025-08-02
                            [end] => 2025-08-02
                        )
                    [jumlah_input] => 
                    [jumlah_data] => 1
                    [rata_rata] => Array
                        (
                            [beli] => 15485.50
                            [jual] => 15561.50
                        )
                    [total_konversi] => 
                    [data] => Array
                        (
                            [0] => Array
                                (
                                    [tanggal] => 2025-08-02
                                    [nilai] => 1
                                    [beli] => 15485.50
                                    [jual] => 15561.50
                                )
                        )
                )
            [1] => Array
                (
                    [mata_uang] => EUR
                    [periode] => Array
                        (
                            [start] => 2025-08-02
                            [end] => 2025-08-02
                        )
                    [jumlah_input] => 
                    [jumlah_data] => 1
                    [rata_rata] => Array
                        (
                            [beli] => 16850.25
                            [jual] => 16927.75
                        )
                    [total_konversi] => 
                    [data] => Array
                        (
                            [0] => Array
                                (
                                    [tanggal] => 2025-08-02
                                    [nilai] => 1
                                    [beli] => 16850.25
                                    [jual] => 16927.75
                                )
                        )
                )
            [2] => Array
                (
                    [mata_uang] => JPY
                    [periode] => Array
                        (
                            [start] => 2025-08-02
                            [end] => 2025-08-02
                        )
                    [jumlah_input] => 
                    [jumlah_data] => 1
                    [rata_rata] => Array
                        (
                            [beli] => 103.45
                            [jual] => 103.89
                        )
                    [total_konversi] => 
                    [data] => Array
                        (
                            [0] => Array
                                (
                                    [tanggal] => 2025-08-02
                                    [nilai] => 100
                                    [beli] => 103.45
                                    [jual] => 103.89
                                )
                        )
                )
        )
)
```

### 3. Mendapatkan Kurs dengan Rentang Tanggal

```php
$result = $kursBI->getKurs(
    'USD',
    '2025-08-01',
    '2025-08-02'
);

print_r($result);
```

**Return Value:**
```php
Array
(
    [success] => 1
    [mata_uang_count] => 1
    [data] => Array
        (
            [0] => Array
                (
                    [mata_uang] => USD
                    [periode] => Array
                        (
                            [start] => 2025-08-01
                            [end] => 2025-08-02
                        )
                    [jumlah_input] => 
                    [jumlah_data] => 2
                    [rata_rata] => Array
                        (
                            [beli] => 15480.25
                            [jual] => 15556.25
                        )
                    [total_konversi] => 
                    [data] => Array
                        (
                            [0] => Array
                                (
                                    [tanggal] => 2025-08-01
                                    [nilai] => 1
                                    [beli] => 15475.00
                                    [jual] => 15551.00
                                )
                            [1] => Array
                                (
                                    [tanggal] => 2025-08-02
                                    [nilai] => 1
                                    [beli] => 15485.50
                                    [jual] => 15561.50
                                )
                        )
                )
        )
)
```

### 4. Mendapatkan Kurs dengan Limit Data

```php
$result = $kursBI->getKurs(
    'USD',
    '2025-07-28',
    '2025-08-02',
    2  // limit hanya 2 data
);

print_r($result);
```

**Return Value:**
```php
Array
(
    [success] => 1
    [mata_uang_count] => 1
    [data] => Array
        (
            [0] => Array
                (
                    [mata_uang] => USD
                    [periode] => Array
                        (
                            [start] => 2025-07-28
                            [end] => 2025-08-02
                        )
                    [jumlah_input] => 
                    [jumlah_data] => 2
                    [rata_rata] => Array
                        (
                            [beli] => 15478.25
                            [jual] => 15554.25
                        )
                    [total_konversi] => 
                    [data] => Array
                        (
                            [0] => Array
                                (
                                    [tanggal] => 2025-07-28
                                    [nilai] => 1
                                    [beli] => 15471.00
                                    [jual] => 15547.00
                                )
                            [1] => Array
                                (
                                    [tanggal] => 2025-07-29
                                    [nilai] => 1
                                    [beli] => 15485.50
                                    [jual] => 15561.50
                                )
                        )
                )
        )
)
```

### 5. Konversi Mata Uang dengan Jumlah Tertentu

```php
$result = $kursBI->getKurs(
    'USD',
    null,  // tanggal hari ini
    null,  // tanggal hari ini
    null,  // tanpa limit
    1000   // konversi $1000
);

print_r($result);
```

**Return Value:**
```php
Array
(
    [success] => 1
    [mata_uang_count] => 1
    [data] => Array
        (
            [0] => Array
                (
                    [mata_uang] => USD
                    [periode] => Array
                        (
                            [start] => 2025-08-02
                            [end] => 2025-08-02
                        )
                    [jumlah_input] => 1000
                    [jumlah_data] => 1
                    [rata_rata] => Array
                        (
                            [beli] => 15485.50
                            [jual] => 15561.50
                        )
                    [total_konversi] => Array
                        (
                            [beli] => 15485500
                            [jual] => 15561500
                        )
                    [data] => Array
                        (
                            [0] => Array
                                (
                                    [tanggal] => 2025-08-02
                                    [nilai] => 1
                                    [beli] => 15485.50
                                    [jual] => 15561.50
                                    [konversi_beli] => 15485500
                                    [konversi_jual] => 15561500
                                )
                        )
                )
        )
)
```

### 6. Penggunaan Kompleks - Multiple Currency dengan Konversi

```php
$result = $kursBI->getKurs(
    'USD,EUR,SGD',
    '2025-08-01',
    '2025-08-02',
    1,     // limit 1 data per mata uang
    500    // konversi 500 unit mata uang
);

print_r($result);
```

## 📚 API Reference

### Method `getKurs()`

```php
public function getKurs(
    string $mataUang = 'USD',
    string $startDate = null,
    string $endDate = null,
    int $limit = null,
    float $jumlah = null
): array
```

#### Parameters

| Parameter | Type | Default | Deskripsi |
|-----------|------|---------|-----------|
| `$mataUang` | `string` | `'USD'` | Kode mata uang (bisa multiple, dipisah koma). Contoh: `'USD'` atau `'USD,EUR,JPY'` |
| `$startDate` | `string\|null` | `null` | Tanggal mulai (format: Y-m-d). Jika null, akan menggunakan tanggal hari ini |
| `$endDate` | `string\|null` | `null` | Tanggal akhir (format: Y-m-d). Jika null, akan menggunakan tanggal hari ini |
| `$limit` | `int\|null` | `null` | Batas jumlah data yang dikembalikan per mata uang |
| `$jumlah` | `float\|null` | `null` | Jumlah nominal untuk konversi mata uang |

#### Return Value

Method ini mengembalikan array dengan struktur:

```php
[
    'success' => bool,           // Status keberhasilan request
    'mata_uang_count' => int,    // Jumlah mata uang yang diminta
    'data' => [                  // Array data kurs per mata uang
        [
            'mata_uang' => string,     // Kode mata uang
            'periode' => [
                'start' => string,     // Tanggal mulai
                'end' => string        // Tanggal akhir
            ],
            'jumlah_input' => float|null,    // Jumlah input untuk konversi
            'jumlah_data' => int,            // Jumlah data kurs
            'rata_rata' => [
                'beli' => float,       // Rata-rata kurs beli
                'jual' => float        // Rata-rata kurs jual
            ],
            'total_konversi' => [      // Hanya ada jika $jumlah tidak null
                'beli' => float,       // Total konversi kurs beli
                'jual' => float        // Total konversi kurs jual
            ] | null,
            'data' => [                // Array detail kurs per tanggal
                [
                    'tanggal' => string,       // Tanggal kurs
                    'nilai' => float,          // Nilai nominal
                    'beli' => float,           // Kurs beli
                    'jual' => float,           // Kurs jual
                    'konversi_beli' => float,  // Hasil konversi beli (jika ada)
                    'konversi_jual' => float   // Hasil konversi jual (jika ada)
                ]
            ]
        ]
    ]
]
```

## ⚙️ Fitur

### Caching
Library ini menggunakan Laravel Cache untuk menyimpan hasil request selama 6 jam. Cache key menggunakan format: `kursbi_{kode_mata_uang}_{start_date}_{end_date}`.

### User-Agent Dinamis
Setiap request menggunakan User-Agent yang digenerate secara random dari kombinasi OS dan browser populer untuk menghindari blocking.

### Error Handling
Jika terjadi error saat mengambil data dari BI, method akan mengembalikan array kosong dan error akan di-report menggunakan fungsi `report()` Laravel.

## 🎯 Mata Uang yang Didukung

Library ini mendukung semua mata uang yang tersedia di API Bank Indonesia, termasuk namun tidak terbatas pada:

- USD (US Dollar)
- EUR (Euro)
- JPY (Japanese Yen)
- GBP (British Pound)
- SGD (Singapore Dollar)
- AUD (Australian Dollar)
- CHF (Swiss Franc)
- CAD (Canadian Dollar)
- HKD (Hong Kong Dollar)
- CNY (Chinese Yuan)
- Dan mata uang lainnya yang didukung BI

## 📋 Requirements

- PHP >= 7.4
- Laravel >= 8.0
- GuzzleHttp/Guzzle
- Laravel Cache

## 🤝 Contributing

Kontribusi selalu diterima! Silakan buat pull request atau buka issue untuk bug report dan feature request.

## 📄 License

Library ini menggunakan lisensi MIT. Lihat file [LICENSE](LICENSE) untuk detail lengkap.

## 🐛 Bug Reports

Jika Anda menemukan bug, silakan buat issue di [GitHub Issues](https://github.com/fyyyn1210/kursbi/issues) dengan informasi:

1. Versi PHP yang digunakan
2. Versi Laravel yang digunakan
3. Code snippet yang menyebabkan error
4. Pesan error yang muncul

## 📞 Support

Jika Anda butuh bantuan atau memiliki pertanyaan, silakan:

- Buka issue di GitHub
- Email: [your-email@example.com]

## 🎉 Credits

- Data kurs dari [Bank Indonesia](https://www.bi.go.id)
- Terima kasih kepada semua kontributor

---

⭐ **Jangan lupa berikan star jika library ini membantu Anda!**
