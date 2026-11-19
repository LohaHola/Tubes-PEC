<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'laptop' => [
                'name' => 'Laptop',
                'description' => 'Laptop untuk produktivitas, kuliah, pekerjaan, dan gaming.',
            ],
            'komponen-pc' => [
                'name' => 'Komponen PC',
                'description' => 'Komponen untuk merakit dan meningkatkan performa komputer.',
            ],
            'monitor' => [
                'name' => 'Monitor',
                'description' => 'Monitor untuk produktivitas, desain, dan hiburan.',
            ],
            'periferal' => [
                'name' => 'Periferal',
                'description' => 'Perangkat pendukung komputer seperti keyboard, mouse, dan webcam.',
            ],
            'audio' => [
                'name' => 'Audio',
                'description' => 'Headphone, headset, dan mikrofon untuk komunikasi serta hiburan.',
            ],
        ];

        $categoryModels = [];
        foreach ($categories as $slug => $data) {
            $categoryModels[$slug] = Category::updateOrCreate(
                ['slug' => $slug],
                $data
            );
        }

        $products = [
            [
                'category' => 'laptop', 'sku' => 'ARB-LTP-001',
                'name' => 'ASUS Vivobook 14 M1405YA',
                'slug' => 'asus-vivobook-14-m1405ya',
                'description' => 'Laptop 14 inci untuk kuliah dan pekerjaan harian dengan prosesor Ryzen 5, memori 16 GB, serta penyimpanan SSD.',
                'price' => 8299000, 'stock' => 8,
                'specs' => ['Prosesor' => 'AMD Ryzen 5 7530U', 'Memori' => '16 GB DDR4', 'Penyimpanan' => '512 GB NVMe SSD', 'Layar' => '14 inci FHD IPS'],
            ],
            [
                'category' => 'laptop', 'sku' => 'ARB-LTP-002',
                'name' => 'Lenovo LOQ 15IRH8',
                'slug' => 'lenovo-loq-15irh8',
                'description' => 'Laptop gaming entry-level dengan sistem pendingin dua kipas dan layar refresh rate tinggi untuk bermain serta produktivitas.',
                'price' => 13499000, 'stock' => 5,
                'specs' => ['Prosesor' => 'Intel Core i5-12450H', 'Grafis' => 'NVIDIA GeForce RTX 3050 6 GB', 'Memori' => '16 GB DDR5', 'Penyimpanan' => '512 GB NVMe SSD', 'Layar' => '15.6 inci FHD 144 Hz'],
            ],
            [
                'category' => 'laptop', 'sku' => 'ARB-LTP-003',
                'name' => 'Acer Aspire 5 A514',
                'slug' => 'acer-aspire-5-a514',
                'description' => 'Laptop ringkas untuk kebutuhan kantor dan pembelajaran dengan prosesor Intel generasi ke-13 serta SSD.',
                'price' => 9199000, 'stock' => 7,
                'specs' => ['Prosesor' => 'Intel Core i5-1335U', 'Memori' => '8 GB DDR4', 'Penyimpanan' => '512 GB NVMe SSD', 'Layar' => '14 inci FHD IPS'],
            ],
            [
                'category' => 'laptop', 'sku' => 'ARB-LTP-004',
                'name' => 'HP Victus 15-fa',
                'slug' => 'hp-victus-15-fa',
                'description' => 'Laptop performa untuk gaming dan kreasi konten dengan GPU diskrit serta layar 144 Hz.',
                'price' => 15999000, 'stock' => 3,
                'specs' => ['Prosesor' => 'Intel Core i5-13420H', 'Grafis' => 'NVIDIA GeForce RTX 4050 6 GB', 'Memori' => '16 GB DDR5', 'Penyimpanan' => '512 GB NVMe SSD', 'Layar' => '15.6 inci FHD 144 Hz'],
            ],
            [
                'category' => 'komponen-pc', 'sku' => 'ARB-CMP-001',
                'name' => 'AMD Ryzen 5 5600',
                'slug' => 'amd-ryzen-5-5600',
                'description' => 'Prosesor desktop 6-core yang cocok untuk PC gaming kelas menengah dan kebutuhan multitasking.',
                'price' => 1699000, 'stock' => 12,
                'specs' => ['Core / Thread' => '6 Core / 12 Thread', 'Base Clock' => '3.5 GHz', 'Max Boost' => '4.4 GHz', 'Socket' => 'AM4', 'TDP' => '65 W'],
            ],
            [
                'category' => 'komponen-pc', 'sku' => 'ARB-CMP-002',
                'name' => 'MSI GeForce RTX 4060 Ventus 2X 8G OC',
                'slug' => 'msi-geforce-rtx-4060-ventus-2x-8g-oc',
                'description' => 'Kartu grafis dua kipas untuk gaming 1080p dan akselerasi aplikasi kreatif.',
                'price' => 5099000, 'stock' => 4,
                'specs' => ['GPU' => 'NVIDIA GeForce RTX 4060', 'Memori' => '8 GB GDDR6', 'Interface' => 'PCI Express 4.0', 'Output' => 'DisplayPort / HDMI'],
            ],
            [
                'category' => 'komponen-pc', 'sku' => 'ARB-CMP-003',
                'name' => 'Kingston FURY Beast DDR4 16 GB',
                'slug' => 'kingston-fury-beast-ddr4-16gb',
                'description' => 'Kit memori desktop 2 x 8 GB untuk meningkatkan kapasitas RAM pada platform DDR4.',
                'price' => 649000, 'stock' => 18,
                'specs' => ['Kapasitas' => '16 GB (2 x 8 GB)', 'Tipe' => 'DDR4', 'Kecepatan' => '3200 MT/s', 'Form Factor' => 'DIMM'],
            ],
            [
                'category' => 'komponen-pc', 'sku' => 'ARB-CMP-004',
                'name' => 'WD Blue SN580 NVMe 1 TB',
                'slug' => 'wd-blue-sn580-nvme-1tb',
                'description' => 'SSD NVMe untuk penyimpanan sistem operasi, aplikasi, dan file kerja dengan respons cepat.',
                'price' => 1199000, 'stock' => 10,
                'specs' => ['Kapasitas' => '1 TB', 'Interface' => 'PCIe Gen 4.0 x4', 'Form Factor' => 'M.2 2280', 'Kecepatan Baca' => 'Hingga 4.150 MB/s'],
            ],
            [
                'category' => 'monitor', 'sku' => 'ARB-MON-001',
                'name' => 'Samsung Odyssey G5 27 inci',
                'slug' => 'samsung-odyssey-g5-27',
                'description' => 'Monitor gaming curved dengan resolusi QHD untuk tampilan lebih luas dan detail.',
                'price' => 3499000, 'stock' => 4,
                'specs' => ['Ukuran' => '27 inci', 'Resolusi' => '2560 x 1440 (QHD)', 'Refresh Rate' => '165 Hz', 'Panel' => 'VA Curved'],
            ],
            [
                'category' => 'monitor', 'sku' => 'ARB-MON-002',
                'name' => 'LG UltraGear 24GN60R-B',
                'slug' => 'lg-ultragear-24gn60r-b',
                'description' => 'Monitor gaming 24 inci dengan panel IPS dan refresh rate 144 Hz.',
                'price' => 1899000, 'stock' => 6,
                'specs' => ['Ukuran' => '23.8 inci', 'Resolusi' => '1920 x 1080 (FHD)', 'Refresh Rate' => '144 Hz', 'Panel' => 'IPS'],
            ],
            [
                'category' => 'monitor', 'sku' => 'ARB-MON-003',
                'name' => 'Xiaomi Monitor A27i',
                'slug' => 'xiaomi-monitor-a27i',
                'description' => 'Monitor IPS 27 inci untuk penggunaan harian, pekerjaan kantor, dan belajar.',
                'price' => 1399000, 'stock' => 8,
                'specs' => ['Ukuran' => '27 inci', 'Resolusi' => '1920 x 1080 (FHD)', 'Refresh Rate' => '100 Hz', 'Panel' => 'IPS'],
            ],
            [
                'category' => 'periferal', 'sku' => 'ARB-PRF-001',
                'name' => 'Logitech G102 LIGHTSYNC',
                'slug' => 'logitech-g102-lightsync',
                'description' => 'Mouse gaming berkabel dengan sensor presisi dan pencahayaan LIGHTSYNC RGB.',
                'price' => 229000, 'stock' => 20,
                'specs' => ['Sensor' => 'Optical Gaming Sensor', 'DPI' => '200–8.000', 'Tombol' => '6 tombol', 'Koneksi' => 'USB-A kabel'],
            ],
            [
                'category' => 'periferal', 'sku' => 'ARB-PRF-002',
                'name' => 'Keychron C3 Pro',
                'slug' => 'keychron-c3-pro',
                'description' => 'Keyboard mekanikal kabel dengan layout ringkas untuk mengetik dan bermain game.',
                'price' => 599000, 'stock' => 7,
                'specs' => ['Layout' => 'TKL 87 tombol', 'Switch' => 'Mechanical Red', 'Koneksi' => 'USB-C kabel', 'Backlight' => 'Red LED'],
            ],
            [
                'category' => 'periferal', 'sku' => 'ARB-PRF-003',
                'name' => 'Logitech Pebble Keys 2 K380s',
                'slug' => 'logitech-pebble-keys-2-k380s',
                'description' => 'Keyboard wireless ringkas dengan koneksi Bluetooth untuk beberapa perangkat.',
                'price' => 499000, 'stock' => 9,
                'specs' => ['Koneksi' => 'Bluetooth Low Energy', 'Perangkat' => 'Hingga 3 perangkat', 'Daya' => '2 baterai AAA', 'Layout' => 'Compact'],
            ],
            [
                'category' => 'periferal', 'sku' => 'ARB-PRF-004',
                'name' => 'Razer DeathAdder Essential',
                'slug' => 'razer-deathadder-essential',
                'description' => 'Mouse ergonomis berkabel dengan lima tombol yang dapat diprogram.',
                'price' => 279000, 'stock' => 12,
                'specs' => ['Sensor' => 'Optical', 'DPI' => 'Hingga 6.400', 'Tombol' => '5 tombol', 'Koneksi' => 'USB kabel'],
            ],
            [
                'category' => 'periferal', 'sku' => 'ARB-PRF-005',
                'name' => 'Logitech C920s Pro HD Webcam',
                'slug' => 'logitech-c920s-pro-hd-webcam',
                'description' => 'Webcam Full HD dengan penutup privasi untuk rapat daring, kelas, dan streaming.',
                'price' => 1199000, 'stock' => 6,
                'specs' => ['Resolusi Video' => 'Full HD 1080p 30 fps', 'Fokus' => 'Autofocus', 'Mikrofon' => 'Stereo', 'Koneksi' => 'USB-A'],
            ],
            [
                'category' => 'audio', 'sku' => 'ARB-AUD-001',
                'name' => 'HyperX Cloud III',
                'slug' => 'hyperx-cloud-iii',
                'description' => 'Headset gaming kabel dengan bantalan nyaman dan mikrofon yang dapat dilepas.',
                'price' => 1499000, 'stock' => 5,
                'specs' => ['Driver' => '53 mm', 'Koneksi' => 'USB / 3.5 mm', 'Mikrofon' => 'Detachable', 'Kompatibilitas' => 'PC dan konsol'],
            ],
            [
                'category' => 'audio', 'sku' => 'ARB-AUD-002',
                'name' => 'JBL Tune 520BT',
                'slug' => 'jbl-tune-520bt',
                'description' => 'Headphone wireless on-ear dengan koneksi Bluetooth dan desain lipat.',
                'price' => 699000, 'stock' => 10,
                'specs' => ['Koneksi' => 'Bluetooth 5.3', 'Daya Tahan Baterai' => 'Hingga 57 jam', 'Desain' => 'On-ear, foldable', 'Pengisian' => 'USB-C'],
            ],
            [
                'category' => 'audio', 'sku' => 'ARB-AUD-003',
                'name' => 'Sony WH-CH720N',
                'slug' => 'sony-wh-ch720n',
                'description' => 'Headphone wireless dengan active noise cancelling untuk mendengarkan audio sehari-hari.',
                'price' => 1499000, 'stock' => 4,
                'specs' => ['Koneksi' => 'Bluetooth', 'Noise Cancelling' => 'Active Noise Cancelling', 'Daya Tahan Baterai' => 'Hingga 35 jam', 'Pengisian' => 'USB-C'],
            ],
            [
                'category' => 'audio', 'sku' => 'ARB-AUD-004',
                'name' => 'FIFINE AM8 USB/XLR Microphone',
                'slug' => 'fifine-am8-usb-xlr-microphone',
                'description' => 'Mikrofon dynamic untuk voice chat, podcast, dan streaming dengan pilihan koneksi USB atau XLR.',
                'price' => 899000, 'stock' => 7,
                'specs' => ['Tipe' => 'Dynamic', 'Pola Pickup' => 'Cardioid', 'Koneksi' => 'USB-C / XLR', 'Kontrol' => 'Mute dan gain'],
            ],
        ];

        foreach ($products as $item) {
            $product = Product::updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'category_id' => $categoryModels[$item['category']]->id,
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'stock' => $item['stock'],
                    'image_path' => null,
                    'is_active' => true,
                ]
            );

            foreach ($item['specs'] as $index => $spec) {
                $product->specifications()->updateOrCreate(
                    ['name' => $index],
                    ['value' => $spec, 'sort_order' => array_search($index, array_keys($item['specs']), true) + 1]
                );
            }
        }
    }
}
