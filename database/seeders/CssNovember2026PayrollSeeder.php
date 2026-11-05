<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\Payroll;
use App\Models\Site;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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

            $expectedEmployeeKeys = [];
            $expectedSiteKeys = [];
            $expectedJobTitleKeys = [];
            foreach (self::EMPLOYEES as [$firstName, $lastName, $jobTitleName, $siteName]) {
                $employeeKey = $this->normalize($firstName.' '.$lastName);
                if (isset($expectedEmployeeKeys[$employeeKey])) {
                    throw new RuntimeException("Le seeder contient plusieurs employés nommés « {$firstName} {$lastName} ».");
                }
                $expectedEmployeeKeys[$employeeKey] = true;
                $expectedSiteKeys[$this->normalize($siteName)] = true;
                $expectedJobTitleKeys[$this->normalize($jobTitleName)] = true;
            }

            $employeesByName = [];
            $deletedEmployees = 0;
            $employeeBlockers = [];
            $employees = Employee::orderByRaw('CASE WHEN company_id = ? THEN 0 ELSE 1 END', [$company->id])
                ->orderBy('id')
                ->get();

            foreach ($employees as $employee) {
                $key = $this->normalize($employee->first_name.' '.$employee->last_name);
                $keepForImport = isset($expectedEmployeeKeys[$key]) && ! isset($employeesByName[$key]);

                if ($keepForImport) {
                    $employeesByName[$key] = $employee;

                    continue;
                }

                $references = $this->employeeReferences($employee);
                if ($references) {
                    $employeeBlockers[] = [
                        'id' => $employee->id,
                        'name' => $employee->full_name,
                        'references' => $references,
                    ];

                    continue;
                }

                $employee->delete();
                $deletedEmployees++;
            }

            $sites = [];
            foreach (Site::where('company_id', $company->id)->get() as $site) {
                $key = $this->normalize($site->name);
                if (! isset($sites[$key])) {
                    $sites[$key] = $site;
                }
            }

            $jobTitles = [];
            foreach (JobTitle::orderBy('id')->get() as $jobTitle) {
                $key = $this->normalize($jobTitle->name);
                if (! isset($jobTitles[$key])) {
                    $jobTitles[$key] = $jobTitle;
                }
            }

            $createdSites = 0;
            $createdJobTitles = 0;
            $createdEmployees = 0;
            $updatedEmployees = 0;
            $createdPayrolls = 0;

            foreach (self::EMPLOYEES as [$firstName, $lastName, $jobTitleName, $siteName, $salary]) {
                $siteKey = $this->normalize($siteName);
                if (! isset($sites[$siteKey])) {
                    $sites[$siteKey] = Site::create([
                        'company_id' => $company->id,
                        'name' => $siteName,
                        'type' => 'bureau',
                        'status' => 'active',
                    ]);
                    $createdSites++;
                }

                $jobTitleKey = $this->normalize($jobTitleName);
                if (! isset($jobTitles[$jobTitleKey])) {
                    $jobTitles[$jobTitleKey] = JobTitle::create([
                        'name' => $jobTitleName,
                        'base_salary' => 0,
                        'status' => 'active',
                    ]);
                    $createdJobTitles++;
                }

                $employeeKey = $this->normalize($firstName.' '.$lastName);
                $attributes = [
                    'company_id' => $company->id,
                    'site_id' => $sites[$siteKey]->id,
                    'job_title_id' => $jobTitles[$jobTitleKey]->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'hire_date' => '2026-11-01',
                    'salary_base' => $salary,
                    'status' => 'active',
                ];

                if (isset($employeesByName[$employeeKey])) {
                    $employee = $employeesByName[$employeeKey];
                    $employee->update($attributes);
                    $updatedEmployees++;
                } else {
                    $employee = Employee::create($attributes);
                    $employeesByName[$employeeKey] = $employee;
                    $createdEmployees++;
                }

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

            $deletedSites = 0;
            $siteBlockers = [];
            foreach (Site::orderBy('id')->get() as $site) {
                $isCanonicalSite = (int) $site->company_id === (int) $company->id
                    && isset($expectedSiteKeys[$this->normalize($site->name)])
                    && $sites[$this->normalize($site->name)]->id === $site->id;

                if ($isCanonicalSite) {
                    continue;
                }

                $references = $this->siteReferences($site);
                if ($references) {
                    $siteBlockers[] = [
                        'id' => $site->id,
                        'name' => $site->name,
                        'references' => $references,
                    ];

                    continue;
                }

                $site->delete();
                $deletedSites++;
            }

            $deletedJobTitles = 0;
            $jobTitleBlockers = [];
            $canonicalJobTitles = [];
            foreach (JobTitle::orderBy('id')->get() as $jobTitle) {
                $key = $this->normalize($jobTitle->name);
                $isCanonicalJobTitle = isset($expectedJobTitleKeys[$key]) && ! isset($canonicalJobTitles[$key]);

                if ($isCanonicalJobTitle) {
                    $canonicalJobTitles[$key] = true;

                    continue;
                }

                $employeeCount = Employee::where('job_title_id', $jobTitle->id)->count();
                if ($employeeCount > 0) {
                    $jobTitleBlockers[] = [
                        'id' => $jobTitle->id,
                        'name' => $jobTitle->name,
                        'employees' => $employeeCount,
                    ];

                    continue;
                }

                $jobTitle->delete();
                $deletedJobTitles++;
            }

            return [
                'company' => $company->name,
                'employees_in_file' => count(self::EMPLOYEES),
                'employees_created' => $createdEmployees,
                'employees_updated' => $updatedEmployees,
                'employees_deleted' => $deletedEmployees,
                'employees_preserved_with_history' => $employeeBlockers,
                'sites_created' => $createdSites,
                'sites_deleted' => $deletedSites,
                'sites_preserved_with_references' => $siteBlockers,
                'job_titles_created' => $createdJobTitles,
                'job_titles_deleted' => $deletedJobTitles,
                'job_titles_preserved_in_use' => $jobTitleBlockers,
                'november_payrolls_created' => $createdPayrolls,
                'payroll_status' => 'pending',
                'advances_and_deductions' => 'none',
                'duplicate_name_rows_excluded' => 6,
            ];
        });

        $this->command?->info(json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    private function employeeReferences(Employee $employee): array
    {
        $references = [];
        foreach ([
            'payrolls',
            'advances',
            'leaves',
            'attendances',
            'salary_adjustments',
            'employee_documents',
            'employee_contracts',
        ] as $table) {
            $count = DB::table($table)->where('employee_id', $employee->id)->count();
            if ($count > 0) {
                $references[$table] = $count;
            }
        }

        return $references;
    }

    private function siteReferences(Site $site): array
    {
        $references = [];
        foreach (['employees', 'stocks', 'orders', 'cash_registers', 'rooms', 'bookings'] as $table) {
            $count = DB::table($table)->where('site_id', $site->id)->count();
            if ($count > 0) {
                $references[$table] = $count;
            }
        }

        return $references;
    }

    private function normalize(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        return preg_replace('/[^a-z0-9]+/', '', strtolower($value));
    }
}
