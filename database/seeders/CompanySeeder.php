<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Site;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Catering Solution & Service ─────────────────────────────────
        $catering = Company::firstOrCreate(
            ['nif' => 'CSS-2024-001'],
            [
                'name'              => 'Catering Solution & Service',
                'legal_form'        => 'SARL',
                'rc'                => 'RC-2024-0001',
                'address'           => 'Nouakchott, Mauritanie',
                'phone'             => '+222 45 00 0001',
                'email'             => 'contact@catering-solution.mr',
                'currency'          => 'MRU',
                'status'            => 'active',
            ]
        );

        Site::firstOrCreate(
            ['company_id' => $catering->id, 'name' => 'Cuisine Centrale Catering'],
            [
                'address' => 'Zone Industrielle, Nouakchott',
                'phone'   => '+222 45 00 0002',
                'type'    => 'catering',
                'status'  => 'active',
            ]
        );

        Site::firstOrCreate(
            ['company_id' => $catering->id, 'name' => 'Dépôt Catering'],
            [
                'address' => 'Zone Industrielle, Nouakchott',
                'phone'   => '+222 45 00 0003',
                'type'    => 'depot',
                'status'  => 'active',
            ]
        );

        // ── 2. Royal Grill ─────────────────────────────────────────────────
        $grill = Company::firstOrCreate(
            ['nif' => 'RG-2024-002'],
            [
                'name'              => 'Royal Grill',
                'legal_form'        => 'SARL',
                'rc'                => 'RC-2024-0002',
                'address'           => 'Tevragh Zeina, Nouakchott',
                'phone'             => '+222 45 00 0010',
                'email'             => 'contact@royalgrill.mr',
                'currency'          => 'MRU',
                'status'            => 'active',
            ]
        );

        Site::firstOrCreate(
            ['company_id' => $grill->id, 'name' => 'Royal Grill — Salle principale'],
            [
                'address' => 'Tevragh Zeina, Nouakchott',
                'phone'   => '+222 45 00 0011',
                'type'    => 'restaurant',
                'status'  => 'active',
            ]
        );

        Site::firstOrCreate(
            ['company_id' => $grill->id, 'name' => 'Royal Grill — Terrasse'],
            [
                'address' => 'Tevragh Zeina, Nouakchott',
                'phone'   => '+222 45 00 0012',
                'type'    => 'restaurant',
                'status'  => 'active',
            ]
        );

        // ── 3. Royal Palm ──────────────────────────────────────────────────
        $palm = Company::firstOrCreate(
            ['nif' => 'RP-2024-003'],
            [
                'name'              => 'Royal Palm',
                'legal_form'        => 'SARL',
                'rc'                => 'RC-2024-0003',
                'address'           => 'Ksar, Nouakchott',
                'phone'             => '+222 45 00 0020',
                'email'             => 'contact@royalpalm.mr',
                'currency'          => 'MRU',
                'status'            => 'active',
            ]
        );

        Site::firstOrCreate(
            ['company_id' => $palm->id, 'name' => 'Royal Palm — Hôtel'],
            [
                'address' => 'Ksar, Nouakchott',
                'phone'   => '+222 45 00 0021',
                'type'    => 'hotel',
                'status'  => 'active',
            ]
        );

        Site::firstOrCreate(
            ['company_id' => $palm->id, 'name' => 'Royal Palm — Restaurant'],
            [
                'address' => 'Ksar, Nouakchott',
                'phone'   => '+222 45 00 0022',
                'type'    => 'restaurant',
                'status'  => 'active',
            ]
        );

        Site::firstOrCreate(
            ['company_id' => $palm->id, 'name' => 'Royal Palm — Salle des fêtes'],
            [
                'address' => 'Ksar, Nouakchott',
                'phone'   => '+222 45 00 0023',
                'type'    => 'bureau',
                'status'  => 'active',
            ]
        );
    }
}
