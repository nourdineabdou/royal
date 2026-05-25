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

    public function employees()
    {
        $this->perm('hr.employees.view');
        $employees = Employee::with('jobTitle')->latest()->paginate(15);
        $jobTitles = JobTitle::orderBy('name')->get();
        return view('hr.employees', compact('employees', 'jobTitles'));
    }

    public function storeEmployee(Request $request)
    {
        $this->perm('hr.employees.create');
        $validated = $request->validate([
            'job_title_id' => 'required|exists:job_titles,id',
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

    public function payroll()
    {
        $this->perm('hr.payroll.view');
        $month     = request('month', now()->month);
        $year      = request('year', now()->year);
        $payrolls  = Payroll::with('employee.jobTitle')
            ->where('month', $month)->where('year', $year)->paginate(15);
        return view('hr.payroll', compact('payrolls', 'month', 'year'));
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
            $advances   = Advance::where('employee_id', $emp->id)
                ->where('status', 'approved')
                ->whereMonth('date', $month)->whereYear('date', $year)->sum('amount');

            $base    = $emp->salary_base ?? $emp->jobTitle->base_salary ?? 0;
            $net     = $base + $bonuses - $deductions - $advances;

            Payroll::create([
                'employee_id'      => $emp->id,
                'month'            => $month,
                'year'             => $year,
                'base_salary'      => $base,
                'bonus'            => $bonuses,
                'deduction'        => $deductions,
                'advance_deduction'=> $advances,
                'net_salary'       => max(0, $net),
                'status'           => 'pending',
            ]);

            // Mark advances as deducted
            Advance::where('employee_id', $emp->id)
                ->where('status', 'approved')
                ->whereMonth('date', $month)->whereYear('date', $year)
                ->update(['status' => 'deducted']);

            $created++;
        }
        return redirect()->route('hr.payroll', ['month' => $month, 'year' => $year])
            ->with('success', "$created fiches de paie générées.");
    }

    public function markPayrollPaid(Payroll $payroll)
    {
        $this->perm('hr.payroll.mark-paid');
        $payroll->update(['status' => 'paid', 'paid_at' => now()]);
        return redirect()->back()->with('success', 'Paie marquée comme payée.');
    }

    public function attendance()
    {
        $this->perm('hr.attendance.view');
        $date        = request('date', today()->toDateString());
        $attendances = Attendance::with('employee.jobTitle')->where('date', $date)->paginate(20);
        $employees   = Employee::where('status', 'active')->orderBy('first_name')->get();
        return view('hr.attendance', compact('attendances', 'date', 'employees'));
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
            'employee_id' => 'required|exists:employees,id',
            'amount'      => 'required|numeric|min:1',
            'date'        => 'required|date',
        ]);
        $validated['status'] = 'pending';
        $advance = Advance::create($validated);

        // Créer une transaction comptable pour l'avance
        Transaction::create([
            'type'      => 'advance',
            'amount'    => $advance->amount,
            'reference' => 'ADV-' . $advance->id,
            'date'      => $advance->date,
            'module'    => 'hr',
            'description' => 'Avance employé(e) #' . $advance->employee_id,
        ]);
        return redirect()->route('hr.advances')->with('success', 'Avance enregistrée.');
    }

    public function approveAdvance(Advance $advance)
    {
        $this->perm('hr.advances.approve');
        $advance->update(['status' => 'approved']);
        return redirect()->back()->with('success', 'Avance approuvée.');
    }
}
