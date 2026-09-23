<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\Payroll;
use App\Models\Leave;
use App\Models\Attendance;
use App\Models\Advance;
use App\Models\SalaryAdjustment;
use App\Models\Site;
use Carbon\Carbon;
use App\Models\Transaction;

class HRController extends Controller
{
    public function dashboard()
    {
        $this->perm('hr.dashboard');
        $today = Carbon::today();
        $currentMonth = $today->month;
        $currentYear = $today->year;

        // Statistiques employés
        $totalEmployees   = Employee::count();
        $activeEmployees  = Employee::where('status', 'active')->count();
        $inactiveEmployees = Employee::where('status', 'inactive')->count();
        $totalJobTitles   = JobTitle::count();

        // Congés
        $pendingLeaves       = Leave::where('status', 'pending')->count();
        $approvedLeavesMonth = Leave::where('status', 'approved')
            ->whereMonth('start_date', $currentMonth)
            ->whereYear('start_date', $currentYear)
            ->count();
        $leavesToday = Leave::where('status', 'approved')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->count();

        // Paie du mois courant
        $payrollsPending    = Payroll::where('status', 'pending')
            ->where('month', $currentMonth)->where('year', $currentYear)->count();
        $payrollsPaid       = Payroll::where('status', 'paid')
            ->where('month', $currentMonth)->where('year', $currentYear)->count();
        $totalPayrollAmount = Payroll::where('month', $currentMonth)
            ->where('year', $currentYear)->sum('net_salary');

        // Présence aujourd'hui
        $presentToday = Attendance::where('date', $today)->where('status', 'present')->count();
        $absentToday  = Attendance::where('date', $today)->where('status', 'absent')->count();
        $lateToday    = Attendance::where('date', $today)->where('status', 'late')->count();

        // Avances
        $pendingAdvances       = Advance::where('status', 'pending')->count();
        $pendingAdvancesAmount = Advance::where('status', 'pending')->sum('amount');

        // Derniers employés
        $recentEmployees = Employee::with('jobTitle')->latest()->take(6)->get();

        // Dernières demandes de congé
        $recentLeaves = Leave::with('employee.jobTitle')->latest()->take(6)->get();

        // Employés par poste
        $employeesByJobTitle = JobTitle::withCount('employees')->get();

        // Paie des 6 derniers mois (graphique)
        $payrollChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $payrollChart[] = [
                'label'  => $date->format('M Y'),
                'amount' => (float) Payroll::where('month', $date->month)
                                ->where('year', $date->year)->sum('net_salary'),
            ];
        }

        // Présence des 7 derniers jours (graphique)
        $attendanceChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $attendanceChart[] = [
                'label'   => $d->format('D d/m'),
                'present' => Attendance::where('date', $d)->where('status', 'present')->count(),
                'absent'  => Attendance::where('date', $d)->where('status', 'absent')->count(),
                'late'    => Attendance::where('date', $d)->where('status', 'late')->count(),
            ];
        }

        return view('hr.dashboard', compact(
            'totalEmployees', 'activeEmployees', 'inactiveEmployees', 'totalJobTitles',
            'pendingLeaves', 'approvedLeavesMonth', 'leavesToday',
            'payrollsPending', 'payrollsPaid', 'totalPayrollAmount',
            'presentToday', 'absentToday', 'lateToday',
            'pendingAdvances', 'pendingAdvancesAmount',
            'recentEmployees', 'recentLeaves',
            'employeesByJobTitle', 'payrollChart', 'attendanceChart'
        ));
    }

    /**
     * Gestion des contrats (CDI/CDD/Stage/Prestation) : période d'essai, date de fin,
     * avec alertes de renouvellement pour les contrats qui arrivent bientôt à échéance.
     */
    public function contracts(Request $request)
    {
        $this->perm('hr.employees.view');

        $status = $request->query('status');

        $contracts = \App\Models\EmployeeContract::with('employee.jobTitle')
            ->when($status === 'ending_soon', function ($q) {
                $q->where('status', 'active')->whereNotNull('end_date')
                  ->whereDate('end_date', '<=', now()->addDays(30))->whereDate('end_date', '>=', now());
            })
            ->when($status === 'expired', function ($q) {
                $q->where('status', 'active')->whereNotNull('end_date')->whereDate('end_date', '<', now());
            })
            ->when($status && !in_array($status, ['ending_soon', 'expired']), fn ($q) => $q->where('status', $status))
            ->latest('start_date')
            ->paginate(15)
            ->withQueryString();

        $endingSoonCount = \App\Models\EmployeeContract::where('status', 'active')->whereNotNull('end_date')
            ->whereDate('end_date', '<=', now()->addDays(30))->whereDate('end_date', '>=', now())->count();
        $expiredCount = \App\Models\EmployeeContract::where('status', 'active')->whereNotNull('end_date')
            ->whereDate('end_date', '<', now())->count();

        $employees = Employee::orderBy('first_name')->get();

        return view('hr.contracts', compact('contracts', 'employees', 'status', 'endingSoonCount', 'expiredCount'));
    }

    public function storeContract(Request $request)
    {
        $this->perm('hr.employees.edit');
        $validated = $request->validate([
            'employee_id'       => 'required|exists:employees,id',
            'type'              => 'required|in:' . implode(',', array_keys(\App\Models\EmployeeContract::TYPES)),
            'start_date'        => 'required|date',
            'end_date'          => 'nullable|date|after:start_date',
            'trial_period_end'  => 'nullable|date|after_or_equal:start_date',
            'salary'            => 'nullable|numeric|min:0',
            'notes'             => 'nullable|string',
        ]);

        // Un nouveau contrat actif remplace l'ancien (renouvellement) — on clôture les précédents actifs.
        \App\Models\EmployeeContract::where('employee_id', $validated['employee_id'])
            ->where('status', 'active')->update(['status' => 'ended']);

        \App\Models\EmployeeContract::create($validated + ['status' => 'active']);

        return back()->with('success', 'Contrat enregistré.');
    }

    public function terminateContract(\App\Models\EmployeeContract $contract)
    {
        $this->perm('hr.employees.edit');
        $contract->update(['status' => 'terminated']);

        return back()->with('success', 'Contrat marqué comme rompu.');
    }

    public function employees(Request $request)
    {
        $this->perm('hr.employees.view');

        $jobTitleId = $request->query('job_title_id');
        $phone      = $request->query('phone');
        $name       = $request->query('name');

        $employees = Employee::with(['jobTitle', 'site', 'documents'])
            ->when($jobTitleId, fn ($q, $v) => $q->where('job_title_id', $v))
            ->when($phone, fn ($q, $v) => $q->where('phone', 'like', '%' . $v . '%'))
            ->when($name, function ($q, $v) {
                $q->where(function ($q2) use ($v) {
                    $q2->where('first_name', 'like', '%' . $v . '%')
                       ->orWhere('last_name', 'like', '%' . $v . '%');
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $jobTitles = JobTitle::orderBy('name')->get();
        $sites = Site::orderBy('name')->get();

        return view('hr.employees', compact('employees', 'jobTitles', 'sites', 'jobTitleId', 'phone', 'name'));
    }

    /**
     * Suivi documentaire : pièce d'identité, diplômes, contrat scanné par employé (optionnel).
     */
    public function storeEmployeeDocument(Request $request, Employee $employee)
    {
        $this->perm('hr.employees.edit');
        $validated = $request->validate([
            'type' => 'required|in:' . implode(',', array_keys(\App\Models\EmployeeDocument::TYPES)),
            'name' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = $request->file('file')->store('employee-documents', 'public');

        \App\Models\EmployeeDocument::create([
            'employee_id' => $employee->id,
            'type'        => $validated['type'],
            'name'        => $validated['name'],
            'file_path'   => $path,
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Document ajouté.');
    }

    public function destroyEmployeeDocument(\App\Models\EmployeeDocument $document)
    {
        $this->perm('hr.employees.edit');
        \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Document supprimé.');
    }

    public function storeEmployee(Request $request)
    {
        $this->perm('hr.employees.create');
        $validated = $request->validate([
            'job_title_id' => 'required|exists:job_titles,id',
            'site_id'      => 'nullable|exists:sites,id',
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'phone'        => 'nullable|string|max:30',
            'address'      => 'nullable|string|max:255',
            'hire_date'    => 'required|date',
            'salary_base'  => 'nullable|numeric|min:0',
            'status'       => 'required|in:active,inactive',
        ]);
        Employee::create($validated);
        return redirect()->route('hr.employees')->with('success', 'Employé ajouté avec succès.');
    }

    public function updateEmployee(Request $request, Employee $employee)
    {
        $this->perm('hr.employees.edit');
        $validated = $request->validate([
            'job_title_id' => 'required|exists:job_titles,id',
            'site_id'      => 'nullable|exists:sites,id',
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'phone'        => 'nullable|string|max:30',
            'address'      => 'nullable|string|max:255',
            'hire_date'    => 'required|date',
            'salary_base'  => 'nullable|numeric|min:0',
            'status'       => 'required|in:active,inactive',
        ]);
        $employee->update($validated);
        return redirect()->route('hr.employees')->with('success', 'Employé modifié avec succès.');
    }

    public function destroyEmployee(Employee $employee)
    {
        $this->perm('hr.employees.delete');
        $employee->delete();
        return redirect()->route('hr.employees')->with('success', 'Employé supprimé.');
    }

    public function leaves()
    {
        $this->perm('hr.leaves.view');
        $leaves = Leave::with('employee.jobTitle')->latest()->paginate(15);
        return view('hr.leaves', compact('leaves'));
    }

    public function storeLeave(Request $request)
    {
        $this->perm('hr.leaves.request');
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type'        => 'required|in:annual,sick,unpaid',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'reason'      => 'nullable|string|max:255',
        ]);
        $validated['days']   = Carbon::parse($validated['start_date'])
                                    ->diffInDays(Carbon::parse($validated['end_date'])) + 1;
        $validated['status'] = 'pending';
        Leave::create($validated);
        return redirect()->route('hr.leaves')->with('success', 'Congé soumis avec succès.');
    }

    public function approveLeave(Leave $leave)
    {
        $this->perm('hr.leaves.approve');
        $leave->update(['status' => 'approved']);
        return redirect()->back()->with('success', 'Congé approuvé.');
    }

    public function rejectLeave(Leave $leave)
    {
        $this->perm('hr.leaves.reject');
        $leave->update(['status' => 'rejected']);
        return redirect()->back()->with('success', 'Congé rejeté.');
    }

    public function payroll(Request $request)
    {
        $this->perm('hr.payroll.view');
        $month      = $request->query('month', now()->month);
        $year       = $request->query('year', now()->year);
        $jobTitleId = $request->query('job_title_id');
        $phone      = $request->query('phone');
        $name       = $request->query('name');

        $filtered = fn () => Payroll::where('month', $month)->where('year', $year)
            ->when($jobTitleId, fn ($q, $v) => $q->whereHas('employee', fn ($q2) => $q2->where('job_title_id', $v)))
            ->when($phone, fn ($q, $v) => $q->whereHas('employee', fn ($q2) => $q2->where('phone', 'like', '%' . $v . '%')))
            ->when($name, function ($q, $v) {
                $q->whereHas('employee', function ($q2) use ($v) {
                    $q2->where(function ($q3) use ($v) {
                        $q3->where('first_name', 'like', '%' . $v . '%')
                           ->orWhere('last_name', 'like', '%' . $v . '%');
                    });
                });
            });

        $payrolls = $filtered()->with('employee.jobTitle')->paginate(15)->withQueryString();

        // Totaux calculés sur l'ensemble filtré (pas seulement la page affichée).
        $totalNet  = $filtered()->sum('net_salary');
        $paidCount = $filtered()->where('status', 'paid')->count();
        $pendCount = $filtered()->where('status', 'pending')->count();

        $jobTitles = JobTitle::orderBy('name')->get();
        $paymentTypes = \App\Models\PaymentType::orderBy('name')->get();

        return view('hr.payroll', compact(
            'payrolls', 'month', 'year', 'jobTitleId', 'phone', 'name', 'jobTitles', 'paymentTypes',
            'totalNet', 'paidCount', 'pendCount'
        ));
    }

    /**
     * Fiche de paie imprimable / téléchargeable (PDF via impression navigateur) pour un bulletin donné.
     */
    public function payslip(Payroll $payroll)
    {
        $this->perm('hr.payroll.view');
        $payroll->load('employee.jobTitle', 'paymentType');
        $company = config('app.company');

        return view('hr.payslip', compact('payroll', 'company'));
    }

    public function generatePayroll(Request $request)
    {
        $this->perm('hr.payroll.generate');
        $month = $request->input('month', now()->month);
        $year  = $request->input('year', now()->year);

        $employees = Employee::where('status', 'active')->get();
        $created = 0;
        foreach ($employees as $emp) {
            $exists = Payroll::where('employee_id', $emp->id)
                ->where('month', $month)->where('year', $year)->exists();
            if ($exists) continue;

            $bonuses    = SalaryAdjustment::where('employee_id', $emp->id)
                ->where('type', 'bonus')
                ->whereMonth('date', $month)->whereYear('date', $year)->sum('amount');
            $deductions = SalaryAdjustment::where('employee_id', $emp->id)
                ->where('type', 'deduction')
                ->whereMonth('date', $month)->whereYear('date', $year)->sum('amount');

            $base = $emp->salary_base ?? $emp->jobTitle->base_salary ?? 0;

            // Avances en cours de remboursement : on déduit ce mois-ci le
            // pourcentage convenu du salaire, plafonné au solde restant,
            // jusqu'à ce que l'avance soit soldée.
            $activeAdvances = Advance::where('employee_id', $emp->id)
                ->where('status', 'approved')
                ->where('remaining_balance', '>', 0)
                ->get();

            $advanceDeduction = 0;
            foreach ($activeAdvances as $adv) {
                $monthly = $adv->monthlyDeductionFor((float) $base);
                if ($monthly <= 0) continue;

                $advanceDeduction += $monthly;
                $newRemaining = round((float) $adv->remaining_balance - $monthly, 2);
                $adv->update([
                    'remaining_balance' => max(0, $newRemaining),
                    'status'            => $newRemaining <= 0 ? 'completed' : 'approved',
                ]);
            }

            $net = $base + $bonuses - $deductions - $advanceDeduction;

            Payroll::create([
                'employee_id'      => $emp->id,
                'month'            => $month,
                'year'             => $year,
                'base_salary'      => $base,
                'bonus'            => $bonuses,
                'deduction'        => $deductions,
                'advance_deduction'=> $advanceDeduction,
                'net_salary'       => max(0, $net),
                'status'           => 'pending',
            ]);

            $created++;
        }
        return redirect()->route('hr.payroll', ['month' => $month, 'year' => $year])
            ->with('success', "$created fiches de paie générées.");
    }

    public function markPayrollPaid(Request $request, Payroll $payroll)
    {
        $this->perm('hr.payroll.mark-paid');
        $validated = $request->validate([
            'payment_type_id' => 'required|exists:payment_types,id',
        ]);

        $payroll->update([
            'status'           => 'paid',
            'paid_at'          => $payroll->paid_at ?? now(),
            'payment_type_id'  => $validated['payment_type_id'],
        ]);

        app(\App\Services\AccountingEntryService::class)->postPayrollPayment($payroll);

        return redirect()->back()->with('success', 'Paie marquée comme payée.');
    }

    public function attendance()
    {
        $this->perm('hr.attendance.view');

        $date   = request('date', today()->toDateString());
        $siteId = request('site_id');

        $sites = Site::orderBy('name')->get();
        $attendances = Attendance::with(['employee.jobTitle', 'employee.site'])
            ->where('date', $date)
            ->when($siteId, function ($query, $siteId) {
                $query->whereHas('employee', function ($query) use ($siteId) {
                    $query->where('site_id', $siteId);
                });
            })
            ->paginate(20);

        $employees = Employee::where('status', 'active')
            ->when($siteId, function ($query, $siteId) {
                $query->where('site_id', $siteId);
            })
            ->orderBy('first_name')
            ->get();

        $selfEmployee = auth()->user()->employee ?? null;
        $selfAttendance = null;
        if ($selfEmployee) {
            $selfAttendance = Attendance::where('employee_id', $selfEmployee->id)
                ->where('date', today())
                ->first();
        }

        return view('hr.attendance', compact('attendances', 'date', 'employees', 'sites', 'siteId', 'selfEmployee', 'selfAttendance'));
    }

    public function clock()
    {
        $selfEmployee = auth()->user()->employee;
        $canView = auth()->user()->can('hr.attendance.view');
        abort_if(!$canView && !$selfEmployee, 403, 'Votre compte n’est pas lié à un employé.');

        $todayAttendance = null;
        if ($selfEmployee) {
            $todayAttendance = Attendance::where('employee_id', $selfEmployee->id)
                ->where('date', today())
                ->first();
        }

        $records = $canView
            ? Attendance::with(['employee.jobTitle', 'employee.site'])->orderBy('date', 'desc')->paginate(20)
            : Attendance::where('employee_id', $selfEmployee->id)->orderBy('date', 'desc')->paginate(15);

        return view('hr.clock', compact('selfEmployee', 'todayAttendance', 'records', 'canView'));
    }

    public function storeAttendance(Request $request)
    {
        $this->perm('hr.attendance.record');
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
            'check_in'    => 'nullable|date_format:H:i',
            'check_out'   => 'nullable|date_format:H:i',
            'shift'       => 'required|in:morning,evening',
            'status'      => 'required|in:present,absent,late',
        ]);
        Attendance::updateOrCreate(
            ['employee_id' => $validated['employee_id'], 'date' => $validated['date']],
            $validated
        );
        return redirect()->back()->with('success', 'Présence enregistrée.');
    }

    public function storeAttendanceClock(Request $request)
    {
        $employee = auth()->user()->employee;
        abort_if(!$employee, 403, 'Votre compte n’est pas lié à un employé.');

        $validated = $request->validate([
            'action' => 'required|in:check_in,check_out',
        ]);

        $attendance = Attendance::firstOrNew([
            'employee_id' => $employee->id,
            'date'        => today(),
        ]);

        $time = now()->format('H:i');
        if ($validated['action'] === 'check_in') {
            $attendance->check_in = $time;
            $attendance->shift = $attendance->shift ?: 'morning';
            $attendance->status = $time > '08:00' ? 'late' : 'present';
        } else {
            $attendance->check_out = $time;
            $attendance->shift = $attendance->shift ?: 'morning';
            if (!$attendance->check_in) {
                $attendance->check_in = $time;
                $attendance->status = 'present';
            } elseif ($attendance->status === 'absent') {
                $attendance->status = 'present';
            }
        }

        $attendance->save();

        return redirect()->route('hr.clock')->with('success', 'Pointage enregistré : ' . ($validated['action'] === 'check_in' ? 'Entrée' : 'Sortie') . ' à ' . $time . '.');
    }

    public function sites()
    {
        $this->perm('hr.employees.view');
        $sites = Site::orderBy('name')->paginate(15);
        return view('hr.sites', compact('sites'));
    }

    public function storeSite(Request $request)
    {
        $this->perm('hr.employees.edit');
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
        ]);
        Site::create($validated);
        return redirect()->route('hr.sites')->with('success', 'Emplacement ajouté.');
    }

    public function updateSite(Request $request, Site $site)
    {
        $this->perm('hr.employees.edit');
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
        ]);
        $site->update($validated);
        return redirect()->route('hr.sites')->with('success', 'Emplacement modifié.');
    }

    public function destroySite(Site $site)
    {
        $this->perm('hr.employees.delete');
        $site->delete();
        return redirect()->route('hr.sites')->with('success', 'Emplacement supprimé.');
    }

    public function advances()
    {
        $this->perm('hr.advances.view');
        $advances  = Advance::with('employee.jobTitle')->latest()->paginate(15);
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();
        return view('hr.advances', compact('advances', 'employees'));
    }

    public function storeAdvance(Request $request)
    {
        $this->perm('hr.advances.request');
        $validated = $request->validate([
            'employee_id'           => 'required|exists:employees,id',
            'amount'                => 'required|numeric|min:1',
            'repayment_percentage'  => 'required|numeric|min:1|max:100',
            'date'                  => 'required|date',
        ]);
        $validated['status'] = 'pending';
        $validated['remaining_balance'] = $validated['amount'];
        $advance = Advance::create($validated);

        // Créer une transaction comptable pour l'avance
        Transaction::create([
            'type'      => 'expense',
            'amount'    => $advance->amount,
            'reference' => 'ADV-' . $advance->id,
            'date'      => $advance->date,
            'module'    => 'hr',
            'description' => 'Avance employé(e) #' . $advance->employee_id,
        ]);

        app(\App\Services\AccountingEntryService::class)->postAdvanceGiven($advance);

        return redirect()->route('hr.advances')->with('success', 'Avance enregistrée.');
    }

    public function approveAdvance(Advance $advance)
    {
        $this->perm('hr.advances.approve');
        $advance->update(['status' => 'approved']);
        return redirect()->back()->with('success', 'Avance approuvée.');
    }
}
