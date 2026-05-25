<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\RoomPayment;
use App\Models\CashRegister;
use App\Models\PaymentType;
use Carbon\Carbon;

class ResidenceController extends Controller
{
    // ─── Dashboard ────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $this->perm('residence.dashboard');

        $totalRooms     = Room::count();
        $available      = Room::where('status', 'available')->count();
        $occupied       = Room::where('status', 'occupied')->count();
        $maintenance    = Room::where('status', 'maintenance')->count();

        $today          = Carbon::today();
        $todayCheckIns  = Booking::whereDate('check_in', $today)->whereIn('status', ['pending'])->count();
        $todayCheckOuts = Booking::whereDate('check_out', $today)->where('status', 'checked_in')->count();

        $monthRevenue   = RoomPayment::whereMonth('paid_at', $today->month)
                            ->whereYear('paid_at', $today->year)
                            ->sum('amount');

        $activeBookings = Booking::with('room.roomType')
                            ->where('status', 'checked_in')
                            ->orderBy('check_out')
                            ->get();

        $recentBookings = Booking::with('room.roomType')
                            ->latest()
                            ->take(8)
                            ->get();

        $openRegister   = CashRegister::where('module', 'residence')
                            ->where('status', 'open')
                            ->first();

        return view('residence.dashboard', compact(
            'totalRooms', 'available', 'occupied', 'maintenance',
            'todayCheckIns', 'todayCheckOuts', 'monthRevenue',
            'activeBookings', 'recentBookings', 'openRegister'
        ));
    }

    // ─── Room Types ───────────────────────────────────────────────────────────

    public function roomTypes()
    {
        $this->perm('residence.rooms.view');
        $roomTypes = RoomType::withCount('rooms')->orderBy('name')->get();
        return view('residence.room-types', compact('roomTypes'));
    }

    public function storeRoomType(Request $request)
    {
        $this->perm('residence.rooms.create');
        $request->validate([
            'name'        => 'required|string|max:100',
            'base_price'  => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);
        RoomType::create($request->only('name', 'base_price', 'description'));
        return back()->with('success', 'Type de chambre créé.');
    }

    public function updateRoomType(Request $request, RoomType $roomType)
    {
        $this->perm('residence.rooms.edit');
        $request->validate([
            'name'        => 'required|string|max:100',
            'base_price'  => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);
        $roomType->update($request->only('name', 'base_price', 'description'));
        return back()->with('success', 'Type mis à jour.');
    }

    public function destroyRoomType(RoomType $roomType)
    {
        $this->perm('residence.rooms.delete');
        $roomType->delete();
        return back()->with('success', 'Type supprimé.');
    }

    // ─── Rooms ────────────────────────────────────────────────────────────────

    public function rooms()
    {
        $this->perm('residence.rooms.view');
        $rooms     = Room::with('roomType', 'activeBooking.payments')->orderBy('number')->get();
        $roomTypes = RoomType::orderBy('name')->get();
        return view('residence.rooms', compact('rooms', 'roomTypes'));
    }

    public function storeRoom(Request $request)
    {
        $this->perm('residence.rooms.create');
        $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'number'       => 'required|string|max:20|unique:rooms,number',
            'floor'        => 'nullable|string|max:10',
        ]);
        Room::create([
            'room_type_id' => $request->room_type_id,
            'number'       => $request->number,
            'floor'        => $request->floor,
            'status'       => 'available',
        ]);
        return back()->with('success', 'Chambre ajoutée.');
    }

    public function updateRoom(Request $request, Room $room)
    {
        $this->perm('residence.rooms.edit');
        $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'number'       => 'required|string|max:20|unique:rooms,number,' . $room->id,
            'floor'        => 'nullable|string|max:10',
            'status'       => 'required|in:available,occupied,maintenance',
        ]);
        $room->update($request->only('room_type_id', 'number', 'floor', 'status'));
        return back()->with('success', 'Chambre mise à jour.');
    }

    public function destroyRoom(Room $room)
    {
        $this->perm('residence.rooms.delete');
        $room->delete();
        return back()->with('success', 'Chambre supprimée.');
    }

    // ─── Calendar ─────────────────────────────────────────────────────────────

    public function calendar(Request $request)
    {
        $this->perm('residence.calendar');

        $year  = (int)($request->year  ?? now()->year);
        $month = (int)($request->month ?? now()->month);
        $date  = Carbon::createFromDate($year, $month, 1);

        $rooms = Room::with(['roomType', 'bookings' => function ($q) use ($date) {
            $q->whereNotIn('status', ['cancelled'])
              ->where('check_in',  '<=', $date->copy()->endOfMonth())
              ->where('check_out', '>=', $date->copy()->startOfMonth());
        }])->orderBy('number')->get();

        $daysInMonth = $date->daysInMonth;
        $prev        = $date->copy()->subMonth();
        $next        = $date->copy()->addMonth();

        return view('residence.calendar', compact('rooms', 'date', 'daysInMonth', 'prev', 'next'));
    }

    // ─── Bookings ─────────────────────────────────────────────────────────────

    public function bookings(Request $request)
    {
        $this->perm('residence.bookings.view');

        $query = Booking::with('room.roomType');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->room_id) {
            $query->where('room_id', $request->room_id);
        }
        if ($request->search) {
            $query->where('customer_name', 'like', '%' . $request->search . '%');
        }

        $bookings = $query->latest()->paginate(20);
        $rooms    = Room::orderBy('number')->get();

        return view('residence.bookings', compact('bookings', 'rooms'));
    }

    public function createBooking(Request $request)
    {
        $this->perm('residence.bookings.create');
        $rooms        = Room::with('roomType')->where('status', 'available')->orderBy('number')->get();
        $selectedRoom = $request->room_id ? Room::with('roomType')->find($request->room_id) : null;
        return view('residence.booking-create', compact('rooms', 'selectedRoom'));
    }

    public function storeBooking(Request $request)
    {
        $this->perm('residence.bookings.create');
        $request->validate([
            'room_id'         => 'required|exists:rooms,id',
            'customer_name'   => 'required|string|max:255',
            'customer_phone'  => 'nullable|string|max:50',
            'identity_number' => 'nullable|string|max:100',
            'num_guests'      => 'nullable|integer|min:1|max:50',
            'check_in'        => 'required|date',
            'check_out'       => 'required|date|after:check_in',
            'notes'           => 'nullable|string|max:1000',
        ]);

        $room    = Room::with('roomType')->findOrFail($request->room_id);
        $nights  = Carbon::parse($request->check_in)->diffInDays(Carbon::parse($request->check_out));
        $total   = $nights * $room->roomType->base_price;

        $booking = Booking::create([
            'room_id'         => $room->id,
            'customer_name'   => $request->customer_name,
            'customer_phone'  => $request->customer_phone,
            'identity_number' => $request->identity_number,
            'num_guests'      => $request->num_guests ?? 1,
            'check_in'        => $request->check_in,
            'check_out'       => $request->check_out,
            'total_amount'    => $total,
            'paid_amount'     => 0,
            'notes'           => $request->notes,
            'status'          => 'pending',
        ]);

        return redirect()->route('residence.bookings.show', $booking)
                         ->with('success', 'Réservation créée.');
    }

    public function showBooking(Booking $booking)
    {
        $this->perm('residence.bookings.view');
        $booking->load('room.roomType', 'details', 'payments.paymentType');
        $paymentTypes = PaymentType::orderBy('name')->get();
        $openRegister = CashRegister::where('module', 'residence')->where('status', 'open')->first();
        return view('residence.booking-show', compact('booking', 'paymentTypes', 'openRegister'));
    }

    public function checkIn(Booking $booking)
    {
        $this->perm('residence.bookings.checkin');
        $booking->update(['status' => 'checked_in']);
        $booking->room->update(['status' => 'occupied']);
        return back()->with('success', 'Check-in effectué.');
    }

    public function checkOut(Booking $booking)
    {
        $this->perm('residence.bookings.checkout');
        $booking->update(['status' => 'checked_out']);
        $booking->room->update(['status' => 'available']);
        return back()->with('success', 'Check-out effectué. Chambre libérée.');
    }

    public function cancelBooking(Booking $booking)
    {
        $this->perm('residence.bookings.cancel');
        $booking->update(['status' => 'cancelled']);
        if (in_array($booking->room->status, ['occupied'])) {
            $booking->room->update(['status' => 'available']);
        }
        return back()->with('success', 'Réservation annulée.');
    }

    public function storeExtra(Request $request, Booking $booking)
    {
        $this->perm('residence.bookings.edit');
        $request->validate([
            'title'       => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        BookingDetail::create([
            'booking_id'  => $booking->id,
            'title'       => $request->title,
            'description' => $request->description,
            'amount'      => $request->amount,
        ]);

        $booking->increment('total_amount', $request->amount);

        return back()->with('success', 'Supplément ajouté.');
    }

    public function storePayment(Request $request, Booking $booking)
    {
        $this->perm('residence.bookings.payment');
        $remaining = $booking->remaining_amount;
        $request->validate([
            'payment_type_id' => 'required|exists:payment_types,id',
            'amount'          => 'required|numeric|min:0.01|max:' . max(0.01, $remaining),
        ]);

        $openRegister = CashRegister::where('module', 'residence')->where('status', 'open')->first();

        RoomPayment::create([
            'booking_id'       => $booking->id,
            'payment_type_id'  => $request->payment_type_id,
            'cash_register_id' => $openRegister?->id,
            'amount'           => $request->amount,
            'paid_at'          => now(),
        ]);

        $booking->increment('paid_amount', $request->amount);

        return back()->with('success', 'Paiement enregistré.');
    }

    // ─── Caisse ───────────────────────────────────────────────────────────────

    public function caisse()
    {
        $this->perm('residence.caisse.view');
        $openRegister = CashRegister::where('module', 'residence')
                            ->where('status', 'open')
                            ->with('user')
                            ->first();
        $registers    = CashRegister::where('module', 'residence')
                            ->with('user')
                            ->latest()
                            ->paginate(15);
        $todayRevenue = RoomPayment::whereDate('paid_at', today())->sum('amount');
        $monthRevenue = RoomPayment::whereMonth('paid_at', now()->month)
                            ->whereYear('paid_at', now()->year)
                            ->sum('amount');

        return view('residence.caisse', compact('openRegister', 'registers', 'todayRevenue', 'monthRevenue'));
    }

    public function openRegister(Request $request)
    {
        $this->perm('residence.caisse.open');
        $request->validate([
            'opening_balance' => 'required|numeric|min:0',
        ]);

        CashRegister::create([
            'user_id'         => auth()->id(),
            'module'          => 'residence',
            'opening_balance' => $request->opening_balance,
            'status'          => 'open',
            'opened_at'       => now(),
        ]);

        return back()->with('success', 'Caisse ouverte.');
    }

    public function closeRegister(Request $request, CashRegister $register)
    {
        $this->perm('residence.caisse.close');
        $request->validate([
            'closing_balance' => 'required|numeric|min:0',
        ]);
        $register->update([
            'closing_balance' => $request->closing_balance,
            'accounting_note' => $request->accounting_note,
            'status'          => 'closed',
            'closed_at'       => now(),
        ]);
        return back()->with('success', 'Caisse clôturée.');
    }
}
