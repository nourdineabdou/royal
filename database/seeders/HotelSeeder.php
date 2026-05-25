<?php

namespace Database\Seeders;

use App\Models\RoomType;
use App\Models\Room;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\PaymentType;
use App\Models\CashRegister;
use App\Models\RoomPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Chambres ─────────────────────────────────────────────────────────
        $standard  = RoomType::where('name', 'Chambre Standard')->first();
        $deluxe    = RoomType::where('name', 'Chambre Deluxe')->first();
        $suiteJr   = RoomType::where('name', 'Suite Junior')->first();
        $suitePres = RoomType::where('name', 'Suite Présidentielle')->first();
        $appt      = RoomType::where('name', 'Appartement')->first();

        if (!$standard) {
            $this->command->warn('⚠️  RoomTypes manquants. Lancez ReferenceDataSeeder d\'abord.');
            return;
        }

        $rooms = [
            // Rez-de-chaussée (floor 0)
            ['number' => '001', 'floor' => 'RDC',   'type' => $standard,  'status' => 'occupied'],
            ['number' => '002', 'floor' => 'RDC',   'type' => $standard,  'status' => 'available'],
            ['number' => '003', 'floor' => 'RDC',   'type' => $deluxe,    'status' => 'available'],
            ['number' => '004', 'floor' => 'RDC',   'type' => $deluxe,    'status' => 'maintenance'],
            // 1er étage
            ['number' => '101', 'floor' => '1er',   'type' => $standard,  'status' => 'occupied'],
            ['number' => '102', 'floor' => '1er',   'type' => $standard,  'status' => 'available'],
            ['number' => '103', 'floor' => '1er',   'type' => $standard,  'status' => 'available'],
            ['number' => '104', 'floor' => '1er',   'type' => $deluxe,    'status' => 'occupied'],
            ['number' => '105', 'floor' => '1er',   'type' => $deluxe,    'status' => 'available'],
            // 2ème étage
            ['number' => '201', 'floor' => '2ème',  'type' => $standard,  'status' => 'available'],
            ['number' => '202', 'floor' => '2ème',  'type' => $standard,  'status' => 'occupied'],
            ['number' => '203', 'floor' => '2ème',  'type' => $deluxe,    'status' => 'available'],
            ['number' => '204', 'floor' => '2ème',  'type' => $deluxe,    'status' => 'available'],
            ['number' => '205', 'floor' => '2ème',  'type' => $suiteJr,   'status' => 'occupied'],
            ['number' => '206', 'floor' => '2ème',  'type' => $suiteJr,   'status' => 'available'],
            // 3ème étage
            ['number' => '301', 'floor' => '3ème',  'type' => $standard,  'status' => 'available'],
            ['number' => '302', 'floor' => '3ème',  'type' => $standard,  'status' => 'available'],
            ['number' => '303', 'floor' => '3ème',  'type' => $deluxe,    'status' => 'occupied'],
            ['number' => '304', 'floor' => '3ème',  'type' => $suiteJr,   'status' => 'available'],
            ['number' => '305', 'floor' => '3ème',  'type' => $suiteJr,   'status' => 'maintenance'],
            // Dernier étage (suites)
            ['number' => '401', 'floor' => '4ème',  'type' => $suiteJr,   'status' => 'available'],
            ['number' => '402', 'floor' => '4ème',  'type' => $suiteJr,   'status' => 'occupied'],
            ['number' => '403', 'floor' => '4ème',  'type' => $suitePres, 'status' => 'available'],
            ['number' => '404', 'floor' => '4ème',  'type' => $suitePres, 'status' => 'available'],
            // Appartements (aile B)
            ['number' => 'A01', 'floor' => 'Aile B','type' => $appt,      'status' => 'occupied'],
            ['number' => 'A02', 'floor' => 'Aile B','type' => $appt,      'status' => 'available'],
            ['number' => 'A03', 'floor' => 'Aile B','type' => $appt,      'status' => 'available'],
        ];

        foreach ($rooms as $r) {
            Room::firstOrCreate(
                ['number' => $r['number']],
                ['room_type_id' => $r['type']->id, 'floor' => $r['floor'], 'status' => $r['status']]
            );
        }

        // ─── Réservations de démonstration ────────────────────────────────────
        $especes     = PaymentType::where('name', 'Espèces')->first();
        $carteBancaire = PaymentType::where('name', 'Carte bancaire')->first();
        $virement    = PaymentType::where('name', 'Virement bancaire')->first();

        // Récupérer un caissier pour les paiements
        $caissier = User::whereHas('roles', fn($q) => $q->where('name', 'caissier'))->first()
                 ?? User::first();

        if (!$caissier) {
            $this->command->warn('⚠️ Aucun caissier trouvé. Réservations créées sans paiement.');
        }

        // Caisse d'exemple (si pas encore créée)
        $cashRegister = CashRegister::firstOrCreate(
            ['user_id' => $caissier?->id, 'shift' => 'morning', 'closing_balance' => null],
            [
                'opening_balance' => 50000,
                'opened_at'       => Carbon::now()->startOfDay(),
                'closed_at'       => null,
            ]
        );

        $bookings = [
            [
                'room_number'    => '001',
                'customer_name'  => 'Ibrahima Diallo',
                'customer_phone' => '+222 46 60 70 80',
                'check_in'       => Carbon::now()->subDays(5)->format('Y-m-d'),
                'check_out'      => Carbon::now()->addDays(3)->format('Y-m-d'),
                'status'         => 'checked_in',
                'details'        => [['title' => 'Minibar', 'description' => 'Consommation minibar', 'amount' => 800]],
                'payment'        => ['type' => $especes, 'amount' => 20000],
            ],
            [
                'room_number'    => '101',
                'customer_name'  => 'Ousmane Sy',
                'customer_phone' => '+222 20 81 92 03',
                'check_in'       => Carbon::now()->subDays(2)->format('Y-m-d'),
                'check_out'      => Carbon::now()->addDays(5)->format('Y-m-d'),
                'status'         => 'checked_in',
                'details'        => [
                    ['title' => 'Room Service', 'description' => 'Commande room service × 3', 'amount' => 2700],
                ],
                'payment'        => ['type' => $carteBancaire, 'amount' => 35000],
            ],
            [
                'room_number'    => '104',
                'customer_name'  => 'Ndeye Fall',
                'customer_phone' => '+222 36 14 25 36',
                'check_in'       => Carbon::now()->subDays(1)->format('Y-m-d'),
                'check_out'      => Carbon::now()->addDays(2)->format('Y-m-d'),
                'status'         => 'checked_in',
                'details'        => [],
                'payment'        => ['type' => $especes, 'amount' => 15000],
            ],
            [
                'room_number'    => '202',
                'customer_name'  => 'Amadou Baldé',
                'customer_phone' => '+222 22 11 33 55',
                'check_in'       => Carbon::now()->subDays(10)->format('Y-m-d'),
                'check_out'      => Carbon::now()->subDays(3)->format('Y-m-d'),
                'status'         => 'checked_out',
                'details'        => [['title' => 'Laverie', 'description' => 'Service blanchisserie', 'amount' => 1500]],
                'payment'        => ['type' => $virement, 'amount' => 40000],
            ],
            [
                'room_number'    => '205',
                'customer_name'  => 'Dr. Hassan Al-Rashid',
                'customer_phone' => '+222 20 55 77 99',
                'check_in'       => Carbon::now()->format('Y-m-d'),
                'check_out'      => Carbon::now()->addDays(7)->format('Y-m-d'),
                'status'         => 'checked_in',
                'details'        => [['title' => 'Transfert aéroport', 'description' => 'Navette aller', 'amount' => 3000]],
                'payment'        => ['type' => $carteBancaire, 'amount' => 90000],
            ],
            [
                'room_number'    => '303',
                'customer_name'  => 'Saliou Ba',
                'customer_phone' => '+222 36 89 01 23',
                'check_in'       => Carbon::now()->addDays(3)->format('Y-m-d'),
                'check_out'      => Carbon::now()->addDays(8)->format('Y-m-d'),
                'status'         => 'pending',
                'details'        => [],
                'payment'        => null,
            ],
            [
                'room_number'    => '402',
                'customer_name'  => 'Mme Coumba Diallo',
                'customer_phone' => '+222 22 45 67 89',
                'check_in'       => Carbon::now()->subDays(3)->format('Y-m-d'),
                'check_out'      => Carbon::now()->addDays(4)->format('Y-m-d'),
                'status'         => 'checked_in',
                'details'        => [
                    ['title' => 'Bouquet floral', 'description' => 'Décoration chambre', 'amount' => 5000],
                    ['title' => 'Room Service', 'description' => 'Petit-déjeuner en chambre × 3', 'amount' => 3600],
                ],
                'payment'        => ['type' => $especes, 'amount' => 60000],
            ],
            [
                'room_number'    => 'A01',
                'customer_name'  => 'Famille Ould Didi',
                'customer_phone' => '+222 20 78 90 12',
                'check_in'       => Carbon::now()->subDays(20)->format('Y-m-d'),
                'check_out'      => Carbon::now()->addDays(10)->format('Y-m-d'),
                'status'         => 'checked_in',
                'details'        => [
                    ['title' => 'Ménage quotidien', 'description' => 'Service ménage × 20 jours', 'amount' => 20000],
                    ['title' => 'Parking privé', 'description' => 'Location parking 1 mois', 'amount' => 8000],
                ],
                'payment'        => ['type' => $virement, 'amount' => 350000],
            ],
        ];

        foreach ($bookings as $b) {
            $room = Room::where('number', $b['room_number'])->first();
            if (!$room) continue;

            $nights = Carbon::parse($b['check_out'])->diffInDays(Carbon::parse($b['check_in']));
            $total  = ($room->roomType?->base_price ?? 5000) * max(1, $nights);

            $booking = Booking::firstOrCreate(
                ['room_id' => $room->id, 'customer_name' => $b['customer_name']],
                [
                    'customer_phone' => $b['customer_phone'],
                    'check_in'       => $b['check_in'],
                    'check_out'      => $b['check_out'],
                    'total_amount'   => $total,
                    'status'         => $b['status'],
                ]
            );

            foreach ($b['details'] as $d) {
                BookingDetail::firstOrCreate(
                    ['booking_id' => $booking->id, 'title' => $d['title']],
                    ['description' => $d['description'], 'amount' => $d['amount']]
                );
            }

            if ($b['payment'] && $caissier && $especes) {
                RoomPayment::firstOrCreate(
                    ['booking_id' => $booking->id, 'payment_type_id' => $b['payment']['type']?->id ?? $especes->id],
                    [
                        'amount'           => $b['payment']['amount'],
                        'cash_register_id' => $cashRegister->id,
                        'paid_at'          => Carbon::now(),
                    ]
                );
            }
        }

        $this->command->info('✅ Hôtel créé : ' . Room::count() . ' chambres, ' . Booking::count() . ' réservations.');
    }
}
