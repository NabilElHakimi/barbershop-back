<?php

namespace App\Http\Controllers;
use App\Models\Booking;

use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return response()->json(Booking::all(), 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // Validate the request
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'telephone' => 'required|string|max:20',
        'appointment_time' => 'required|date',
    ]);

    // Check if the requested appointment time already has a reservation
    $existingReservation = Booking::where('appointment_time', $validatedData['appointment_time'])->first();
    if ($existingReservation) {
        return response()->json([
            'message' => 'The requested time is already booked. Please choose another time.',
        ], 409); // HTTP 409 Conflict
    }

    // Check if the requested time is at least 3 hours from now
    $appointmentTime = \Carbon\Carbon::parse($validatedData['appointment_time']);
    $now = \Carbon\Carbon::now();

    if ($appointmentTime->lt($now->addHours(3))) {
        return response()->json([
            'message' => 'Reservations must be made at least 3 hours in advance.',
        ], 422); // HTTP 422 Unprocessable Content
    }

    // Create the reservation if all conditions are met
    $booking = Booking::create($validatedData);

    return response()->json($booking, 201); // HTTP 201 Created
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        return response()->json($booking, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
{
    // Find the booking
    $booking = Booking::find($id);

    if (!$booking) {
        return response()->json(['message' => 'Booking not found'], 404);
    }

    // Validate the request data
    $validatedData = $request->validate([
        'name' => 'sometimes|string|max:255',
        'email' => 'sometimes|email|max:255',
        'telephone' => 'sometimes|string|max:20 ',
        'appointment_time' => 'sometimes|date',
    ]);

    // Check if the updated appointment time already has another reservation
    if (isset($validatedData['appointment_time'])) {
        $existingReservation = Booking::where('appointment_time', $validatedData['appointment_time'])
            ->where('id', '!=', $id) // Exclude the current booking
            ->first();

        if ($existingReservation) {
            return response()->json([
                'message' => 'The requested time is already booked by another reservation. Please choose another time.',
            ], 409); // HTTP 409 Conflict
        }

        // Check if the updated time is at least 3 hours from now
        $appointmentTime = \Carbon\Carbon::parse($validatedData['appointment_time']);
        $now = \Carbon\Carbon::now();

        if ($appointmentTime->lt($now->addHours(3))) {
            return response()->json([
                'message' => 'Reservations must be updated to a time at least 3 hours in advance.',
            ], 422); // HTTP 422 Unprocessable Content
        }
    }

    // Update the booking with validated data
    $booking->update($validatedData);

    return response()->json($booking, 200); // HTTP 200 OK
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        $booking->delete();

        return response()->json(['message' => 'Booking deleted successfully'], 204);
    }
}
