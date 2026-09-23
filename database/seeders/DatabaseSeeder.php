<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderPart;
use App\Models\Sparepart;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users (Admin & Teknisi)
        $admin = User::updateOrCreate(
            ['email' => 'admin@tracket.test'],
            [
                'name' => 'Ahmad Fahrur (Admin & Kasir)',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $technician = User::updateOrCreate(
            ['email' => 'teknisi@tracket.test'],
            [
                'name' => 'Budi Santoso (Teknisi Utama)',
                'password' => Hash::make('password'),
                'role' => 'technician',
            ]
        );

        // 2. Customers
        $c1 = Customer::updateOrCreate(['phone' => '081234567890'], [
            'name' => 'Hendra Pratama',
            'address' => 'Jl. Merdeka No. 12, Jakarta Pusat',
        ]);

        $c2 = Customer::updateOrCreate(['phone' => '085678901234'], [
            'name' => 'Siti Rahmawati',
            'address' => 'Jl. Mawar Indah No. 45, Bandung',
        ]);

        $c3 = Customer::updateOrCreate(['phone' => '087811223344'], [
            'name' => 'Dian Kusuma',
            'address' => 'Perum Griya Lestari Blok B-8, Surabaya',
        ]);

        $c4 = Customer::updateOrCreate(['phone' => '089699887766'], [
            'name' => 'Rizky Fadillah',
            'address' => 'Jl. Pemuda Kav. 9, Semarang',
        ]);

        $c5 = Customer::updateOrCreate(['phone' => '082155667788'], [
            'name' => 'Maya Anggraini',
            'address' => 'Jl. Malioboro No. 30, Yogyakarta',
        ]);

        // 3. Spareparts
        $parts = [
            ['part_code' => 'LCD-IPH13', 'name' => 'LCD Screen OLED iPhone 13 Original', 'category' => 'Layar / LCD', 'stock' => 4, 'buy_price' => 1100000, 'sell_price' => 1450000],
            ['part_code' => 'LCD-ASUS15', 'name' => 'Panel LED 15.6" IPS 144Hz FHD ASUS TUF', 'category' => 'Layar / LCD', 'stock' => 2, 'buy_price' => 850000, 'sell_price' => 1200000],
            ['part_code' => 'BAT-MBP16', 'name' => 'Baterai MacBook Pro M1 16 Inch A2485', 'category' => 'Baterai', 'stock' => 3, 'buy_price' => 900000, 'sell_price' => 1350000],
            ['part_code' => 'BAT-SAM-S22', 'name' => 'Baterai Samsung Galaxy S22 Ultra 5000mAh', 'category' => 'Baterai', 'stock' => 1, 'buy_price' => 280000, 'sell_price' => 450000],
            ['part_code' => 'KBD-THKPAD', 'name' => 'Keyboard Lenovo ThinkPad T480 Backlit US', 'category' => 'Keyboard', 'stock' => 5, 'buy_price' => 250000, 'sell_price' => 400000],
            ['part_code' => 'SSD-NVME-1TB', 'name' => 'SSD NVMe M.2 1TB PCIe 4.0 Kingston KC3000', 'category' => 'Storage', 'stock' => 7, 'buy_price' => 820000, 'sell_price' => 1100000],
            ['part_code' => 'RAM-DDR4-8GB', 'name' => 'RAM SODIMM DDR4 8GB 3200MHz Kingston', 'category' => 'Memori', 'stock' => 8, 'buy_price' => 270000, 'sell_price' => 390000],
            ['part_code' => 'THM-PASTE-TG', 'name' => 'Thermal Paste Thermal Grizzly Kryonaut 1g', 'category' => 'Maintenance', 'stock' => 12, 'buy_price' => 75000, 'sell_price' => 125000],
            ['part_code' => 'IC-PWR-MAX', 'name' => 'IC Power Management PMIC Universal QFN', 'category' => 'IC / Komponen', 'stock' => 0, 'buy_price' => 65000, 'sell_price' => 180000],
            ['part_code' => 'FLX-CHG-IPH', 'name' => 'Flexible Port Charger Lightning iPhone 12', 'category' => 'Kabel & Port', 'stock' => 4, 'buy_price' => 95000, 'sell_price' => 220000],
        ];

        $createdParts = [];
        foreach ($parts as $p) {
            $createdParts[$p['part_code']] = Sparepart::updateOrCreate(
                ['part_code' => $p['part_code']],
                $p
            );
        }

        // 4. Service Orders (covering each workflow status)
        // Order 1: Completed with active warranty
        $srv1 = ServiceOrder::updateOrCreate(
            ['service_code' => 'SRV-202609-0001'],
            [
                'customer_id' => $c1->id,
                'technician_id' => $technician->id,
                'device_name' => 'Apple iPhone 13 128GB Midnight',
                'device_serial' => 'F2LWX891MD6P',
                'issue_description' => 'Layar retak parah, timbul garis hijau vertikal dan sentuhan meloncat-loncat (ghost touch).',
                'accessories_included' => 'Unit + Softcase Hitam',
                'status' => 'completed',
                'labor_cost' => 150000,
                'total_cost' => 1600000,
                'warranty_days' => 30,
                'warranty_expires_at' => Carbon::today()->addDays(25),
                'technician_notes' => 'Penggantian layar LCD OLED original berhasil. Face ID dan TrueTone berfungsi normal.',
            ]
        );
        ServiceOrderPart::updateOrCreate(
            ['service_order_id' => $srv1->id, 'sparepart_id' => $createdParts['LCD-IPH13']->id],
            ['quantity' => 1, 'unit_price' => 1450000, 'subtotal' => 1450000]
        );

        // Order 2: Ready for pickup (Siap Diambil)
        $srv2 = ServiceOrder::updateOrCreate(
            ['service_code' => 'SRV-202609-0002'],
            [
                'customer_id' => $c2->id,
                'technician_id' => $technician->id,
                'device_name' => 'ASUS TUF Gaming A15 FA506',
                'device_serial' => 'N7NRCX001923',
                'issue_description' => 'Mati total setelah terkena cipratan kopi. Layar berkedip hitam sebelum padam.',
                'accessories_included' => 'Unit Laptop + Charger Original 200W + Tas Ransel',
                'status' => 'ready',
                'labor_cost' => 250000,
                'total_cost' => 1450000,
                'warranty_days' => 0,
                'warranty_expires_at' => null,
                'technician_notes' => 'Pembersihan sirkuit ultrasonik dari residu korosi berhasil. Modul panel IPS diganti baru dan stress test GPU tembus 30 menit stabil.',
            ]
        );
        ServiceOrderPart::updateOrCreate(
            ['service_order_id' => $srv2->id, 'sparepart_id' => $createdParts['LCD-ASUS15']->id],
            ['quantity' => 1, 'unit_price' => 1200000, 'subtotal' => 1200000]
        );

        // Order 3: In Progress (Sedang Pengerjaan)
        $srv3 = ServiceOrder::updateOrCreate(
            ['service_code' => 'SRV-202609-0003'],
            [
                'customer_id' => $c3->id,
                'technician_id' => $technician->id,
                'device_name' => 'MacBook Pro 16" M1 Pro Space Gray',
                'device_serial' => 'C02G45XPQ6L7',
                'issue_description' => 'Notifikasi "Service Recommended", baterai cepat drop dari 80% ke 10% dalam 20 menit.',
                'accessories_included' => 'Unit MacBook + MagSafe 3 Cable',
                'status' => 'in_progress',
                'labor_cost' => 200000,
                'total_cost' => 1550000,
                'warranty_days' => 0,
                'warranty_expires_at' => null,
                'technician_notes' => 'Unit sudah dibongkar, baterai lama dilepas dari sasis. Sedang pemasangan baterai baru OEM grade A.',
            ]
        );
        ServiceOrderPart::updateOrCreate(
            ['service_order_id' => $srv3->id, 'sparepart_id' => $createdParts['BAT-MBP16']->id],
            ['quantity' => 1, 'unit_price' => 1350000, 'subtotal' => 1350000]
        );

        // Order 4: Diagnosing (Sedang Diagnosa)
        ServiceOrder::updateOrCreate(
            ['service_code' => 'SRV-202609-0004'],
            [
                'customer_id' => $c4->id,
                'technician_id' => $technician->id,
                'device_name' => 'Lenovo ThinkPad T480 Intel Core i7',
                'device_serial' => 'PF19A822',
                'issue_description' => 'Tombol spasi, backspace, dan enter sering tidak merespons. Trackpoint kadang drift.',
                'accessories_included' => 'Unit Laptop saja',
                'status' => 'diagnosing',
                'labor_cost' => 100000,
                'total_cost' => 100000,
                'warranty_days' => 0,
                'warranty_expires_at' => null,
                'technician_notes' => 'Jalur membran keyboard kotor/short. Disarankan ganti 1 modul keyboard backlight baru.',
            ]
        );

        // Order 5: Pending (Antrian Baru Masuk)
        ServiceOrder::updateOrCreate(
            ['service_code' => 'SRV-202609-0005'],
            [
                'customer_id' => $c5->id,
                'technician_id' => null,
                'device_name' => 'Samsung Galaxy S22 Ultra Phantom Black',
                'device_serial' => 'R5CT409KMLD',
                'issue_description' => 'Backdoor belakang sedikit terangkat karena baterai menggelembung. Suhu unit cepat panas saat dicas.',
                'accessories_included' => 'Unit HP + Dus Box',
                'status' => 'pending',
                'labor_cost' => 0,
                'total_cost' => 0,
                'warranty_days' => 0,
                'warranty_expires_at' => null,
                'technician_notes' => null,
            ]
        );
    }
}
