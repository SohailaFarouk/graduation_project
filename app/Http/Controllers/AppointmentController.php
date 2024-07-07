<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    public function index()
    {
        $user_id = request()->header('user_id');
        $appointment = DB::table('doctor_appointment')
            ->where('user_id', $user_id)
            ->first(['appointment_id']);
        $appointment_id = $appointment->appointment_id;
        $appointments = Appointment::where('appointment_id', $appointment_id)->get();

        return response()->json(['success' => true, 'appointments' => $appointments]);
    }

    /* -------------------------------------------------------------------------- */
    public function store(Request $request)
    {
        $user_id = $request->header('user_id');

        $validator = Validator::make($request->all(), [
            'appointment_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()], 422);
        }

        $appointment = new Appointment();
        $appointment->appointment_date = $request->input('appointment_date');
        $appointment->save();

        $doctorId = $user_id;
        $appointment->doctors()->attach($doctorId);

        return response()->json(['success' => true, 'message' => 'Appointment created successfully', 'appointment_data' => $appointment]);
    }
    /* -------------------------------------------------------------------------- */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_id' => 'required|integer|exists:doctor_appointment,appointment_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $appointment_id = $request->input('appointment_id');
        $appointment = Appointment::find($appointment_id);

        if (!$appointment) {
            return response()->json(['success' => false, 'error' => 'Appointment not found'], 404);
        }

        $appointment->doctors()->detach();

        $appointment->delete();

        return response()->json(['success' => true, 'message' => 'Appointment deleted successfully']);
    }
}
