<?php

namespace Database\Seeders;

use App\Models\Advance;
use App\Models\Company;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\Payroll;
use App\Models\Site;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class CssNovember2026PayrollSeeder extends Seeder
{
    private const EMPLOYEES = [
        ['SALAMATA', 'BALL', 'RAF', 'ADMINISTRATION', 25000],
        ['SAMBA', 'TANDIA', "Resp d'exploitation", 'ADMINISTRATION', 30000],
        ['ABDOULLAH', 'CHEIKH', 'Comptable', 'ADMINISTRATION', 10000],
        ['Sambou', '', 'Econome', 'EMPLOYES ADMINISTRATION', 17000],
        ['Youba', '', 'Asst econome', 'EMPLOYES ADMINISTRATION', 10000],
        ['Fatimetou', 'maham', 'Cheffe cuisine africaine', 'PERSONNEL CUISINE', 22000],
        ['Amineta', 'sidi sarr', 'Cuisinière', 'PERSONNEL CUISINE', 13000],
        ['Boris', '', 'Chef', 'PERSONNEL CUISINE', 24000],
        ['Moise', '', 'Aide cuisine', 'PERSONNEL CUISINE', 19000],
        ['Souleymane', '', 'Aide cuisine', 'PERSONNEL CUISINE', 12000],
        ['Hernestine', '', 'Aide cuisine', 'PERSONNEL CUISINE', 9000],
        ['Mamadou', '', 'Aide cuisine', 'PERSONNEL CUISINE', 15000],
        ['Nene', '', 'Menage', 'PERSONNEL CUISINE', 7000],
        ['Jacob', '', 'Plongeur', 'PERSONNEL CUISINE', 6000],
        ['Brahim', '', 'Plongeur', 'PERSONNEL CUISINE', 6000],
        ['Chouaibou', '', 'Aide cuisine', 'PERSONNEL CUISINE', 10000],
        ['Daouda', 'Sidibé', 'Aide cuisine', 'PERSONNEL CUISINE', 10000],
        ['YANN', '', 'Plongeur', 'PERSONNEL CUISINE', 6000],
        ['Said', '', 'RESPONSABLE TCN', 'PERSONNEL PORT', 35000],
        ['Tijane', '', 'SERVEUR', 'PERSONNEL PORT', 7500],
        ['Cheikh', 'brahim', 'SERVEUR', 'PERSONNEL PORT', 6500],
        ['Amadou', '', 'SERVEUR', 'PERSONNEL PORT', 6500],
        ['Aboubacar', '', 'SERVEUR', 'PERSONNEL PORT', 6500],
        ['Biram', 'hacen', 'SERVEUR', 'PERSONNEL PORT', 8000],
        ['Mamadou', 'djibi', 'SERVEUR', 'PERSONNEL PORT', 8000],
        ['Brahim', '(frere Djibril) ACTEMIUM', 'SERVEUR', 'PERSONNEL PORT', 7500],
        ['Fatma', 'sidi aly', 'RESP SERVICE', 'ECOLE IMANE', 20000],
        ['Monica', '(Meuneu)', 'SERVEUSE', 'ECOLE IMANE', 7800],
        ['ABDELLAHI', '', 'CHAUFFEUR', 'CHAUFFEUR', 10000],
        ['DJIBRIL', 'IVIKOU', 'CHAUFFEUR', 'CHAUFFEUR', 10000],
        ['Ahmedou', 'tijanie', 'SERVEUR', 'PERSONNEL BMI', 10000],
        ['Adama', '', 'SERVEUSE', 'PERSONNEL BMI', 10000],
        ['DAOUDA', '', 'SERVEUR', 'PERSONNEL BMI', 2000],
        ['Habib(frere', 'Moussa)', 'Plongeur', 'PERSONNEL BMI', 5500],
        ['Mamy', '', 'SERVEUSE', 'PERSONNEL BMI', 6000],
        ['OUSMANE', 'FALL', 'SERVEUR', 'PERSONNEL BNM', 8500],
        ['SAMBA', '', 'PLONGEUR', 'PERSONNEL BNM', 7000],
        ['Fatima', '', 'SERVEUSE', 'PERSONNEL BNM', 8000],
        ['AIDA', '', 'RESP SERVICES', 'PERSONNEL BEA', 10000],
        ['SELKA', '', 'SERVEUSE', 'PERSONNEL BEA', 6500],
        ['YAHYA', '', 'RESP SERVICES', 'PERSONNEL KENZ', 8500],
        ['ZEINA', '', 'SERVEUR', 'PERSONNEL KENZ', 6000],
        ['ASSIA', '', 'SERVEUSE', 'PERSONNEL KENZ', 8000],
        ['MOHAMED', '(stagiaire)', 'SERVEUR', 'PERSONNEL KENZ', 5000],
        ['CHEIKH', '', 'RAMASSEUR ORDURE', 'PERSONNEL KENZ', 3000],
        ['OUSMANE', 'COULIBALY', 'SECURITE', 'PERSONNEL KENZ', 7000],
        ['Modou', 'Ba', 'SECURITE', 'PERSONNEL KENZ', 8500],
    ];

    public function run(): void
    {
        $summary = DB::transaction(function (): array {
            $company = Company::where('name', 'Catering Solution & Service')->first();

            if (! $company) {
                throw new RuntimeException('Entreprise Catering Solution & Service introuvable. Vérifiez les seeders de sociétés.');
            }

            $cleanup = $this->clearExistingHrData();

            $sites = [];
            $jobTitles = [];
            $createdPayrolls = 0;

            foreach (self::EMPLOYEES as [$firstName, $lastName, $jobTitleName, $siteName, $salary]) {
                $siteKey = $this->normalize($siteName);
                if (isset($sites[$siteKey])) {
                    $site = $sites[$siteKey];
                } else {
                    $site = Site::create([
                        'company_id' => $company->id,
                        'name' => $siteName,
                        'type' => 'bureau',
                        'status' => 'active',
                    ]);
                    $sites[$siteKey] = $site;
                }

                $jobTitleKey = $this->normalize($jobTitleName);
                if (isset($jobTitles[$jobTitleKey])) {
                    $jobTitle = $jobTitles[$jobTitleKey];
                } else {
                    $jobTitle = JobTitle::create([
                        'name' => $jobTitleName,
                        'base_salary' => 0,
                        'status' => 'active',
                    ]);
                    $jobTitles[$jobTitleKey] = $jobTitle;
                }

                $employee = Employee::create([
                    'company_id' => $company->id,
                    'site_id' => $site->id,
                    'job_title_id' => $jobTitle->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'hire_date' => '2026-11-01',
                    'salary_base' => $salary,
                    'status' => 'active',
                ]);

                $payroll = Payroll::firstOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'month' => 11,
                        'year' => 2026,
                    ],
                    [
                        'company_id' => $company->id,
                        'base_salary' => $salary,
                        'bonus' => 0,
                        'deduction' => 0,
                        'advance_deduction' => 0,
                        'net_salary' => $salary,
                        'status' => 'pending',
                    ]
                );

                if ($payroll->wasRecentlyCreated) {
                    $createdPayrolls++;
                }
            }

            return array_merge([
                'company' => $company->name,
                'employees_imported' => count(self::EMPLOYEES),
                'sites_created' => count($sites),
                'job_titles_created' => count($jobTitles),
                'november_payrolls_created' => $createdPayrolls,
                'payroll_status' => 'pending',
                'advances_and_deductions' => 'none',
                'duplicate_name_rows_excluded' => 6,
            ], $cleanup);
        });

        $documentPaths = $summary['_employee_document_paths'];
        unset($summary['_employee_document_paths']);

        $deletedDocumentFiles = 0;
        $missingDocumentFiles = 0;
        foreach ($documentPaths as $path) {
            $disk = Storage::disk('public');
            if (! $disk->exists($path)) {
                $missingDocumentFiles++;

                continue;
            }

            if (! $disk->delete($path)) {
                throw new RuntimeException("Impossible de supprimer le document employé « {$path} » du stockage public.");
            }

            $deletedDocumentFiles++;
        }

        $summary['employee_document_files_deleted'] = $deletedDocumentFiles;
        $summary['employee_document_files_already_missing'] = $missingDocumentFiles;
        $this->command?->info(json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    private function clearExistingHrData(): array
    {
        $siteBlockers = [];
        foreach (['stocks', 'orders', 'cash_registers', 'rooms', 'bookings'] as $table) {
            $count = DB::table($table)->whereNotNull('site_id')->count();
            if ($count > 0) {
                $siteBlockers[$table] = $count;
            }
        }

        $payrollTransactionIds = DB::table('payrolls')->whereNotNull('transaction_id')->pluck('transaction_id');
        $hrTransactionIds = DB::table('transactions')
            ->where(function ($query) {
                $query->where('module', 'hr')
                    ->orWhere('type', 'salary');
            })
            ->pluck('id')
            ->merge($payrollTransactionIds)
            ->unique()
            ->values();

        $transactionBlockers = [];
        if ($hrTransactionIds->isNotEmpty()) {
            foreach (['payments', 'bookings', 'room_payments', 'events', 'catering_consumptions'] as $table) {
                if (! Schema::hasColumn($table, 'transaction_id')) {
                    continue;
                }

                $count = DB::table($table)->whereIn('transaction_id', $hrTransactionIds)->count();
                if ($count > 0) {
                    $transactionBlockers[$table] = $count;
                }
            }
        }

        if ($siteBlockers || $transactionBlockers) {
            $details = [];
            if ($siteBlockers) {
                $details[] = 'emplacements encore utilisés : '.json_encode($siteBlockers, JSON_UNESCAPED_UNICODE);
            }
            if ($transactionBlockers) {
                $details[] = 'transactions RH liées à d’autres modules : '.json_encode($transactionBlockers, JSON_UNESCAPED_UNICODE);
            }

            throw new RuntimeException('Nettoyage annulé sans modification. '.implode(' ; ', $details));
        }

        $documentPaths = DB::table('employee_documents')->pluck('file_path')->all();
        $journalEntries = DB::table('journal_entries')
            ->where(function ($query) use ($hrTransactionIds) {
                $query->whereIn('source_type', [Payroll::class, Advance::class]);

                if ($hrTransactionIds->isNotEmpty()) {
                    $query->orWhere(function ($query) use ($hrTransactionIds) {
                        $query->where('source_type', Transaction::class)
                            ->whereIn('source_id', $hrTransactionIds);
                    });
                }
            });
        $deletedJournalEntries = $journalEntries->count();
        $journalEntries->delete();

        $deleted = [];
        foreach ([
            'employee_contracts',
            'employee_documents',
            'attendances',
            'advances',
            'leaves',
            'salary_adjustments',
            'payrolls',
            'employees',
            'job_titles',
            'sites',
        ] as $table) {
            $deleted[$table] = DB::table($table)->count();
            DB::table($table)->delete();
        }

        $deleted['transaction_details'] = DB::table('transaction_details')
            ->whereIn('transaction_id', $hrTransactionIds)
            ->count();
        DB::table('transaction_details')->whereIn('transaction_id', $hrTransactionIds)->delete();

        $deleted['hr_transactions'] = $hrTransactionIds->count();
        if ($hrTransactionIds->isNotEmpty()) {
            DB::table('transactions')->whereIn('id', $hrTransactionIds)->delete();
        }

        return [
            'old_records_deleted' => $deleted,
            'old_payroll_and_advance_journal_entries_deleted' => $deletedJournalEntries,
            '_employee_document_paths' => $documentPaths,
        ];
    }

    private function normalize(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        return preg_replace('/[^a-z0-9]+/', '', strtolower($value));
    }
}
