<?php
namespace App\Http\Controllers;

use App\Models\Session;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SessionController extends Controller
{
    public function index()
    {
        $appointments = DB::table('doctor_appointment')->distinct()->pluck('appointment_id');

        $sessionData = [];
        foreach ($appointments as $appointment_id) {

            $sessions = Session::where('appointment_id', $appointment_id)
                                ->whereNull('user_id')  // Filter out reserved sessions
                                ->get();

            $doctors = DB::table('doctor_appointment')
                ->join('doctors', 'doctor_appointment.user_id', '=', 'doctors.user_id')
                ->join('users', 'doctors.user_id', '=', 'users.user_id')
                ->where('doctor_appointment.appointment_id', $appointment_id)
                ->select('doctors.*', 'users.first_name', 'users.last_name','users.image')
                ->get();

            foreach ($sessions as $session) {
                $sessionData[] = [
                    'session' => $session,
                    'doctors' => $doctors->where('session_id', $session->id)->values(),
                ];
            }

        }

        return response()->json([
            'success' => true,
            'sessions_with_doctors' => $sessionData,
            'count' => count($sessionData)
        ]);
    }

    /* -------------------------------------------------------------------------- */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_id' => 'required|exists:appointments,appointment_id',
            'session_date' => 'required|date',
            'session_fees' => 'required|numeric',
            'session_time' => 'required|date_format:H:i',
            'session_type' => 'required|in:Therapy,Psychiatry,Physiatry,Prosthetics'
        ]);

        if ($validator->fails()) {
            return response()->json(['success'=> false ,'error' => $validator->errors()], 422);
        }

        // Find the existing appointment
        $appointment = Appointment::find($request->input('appointment_id'));

        // Check if appointment exists (optional)
        if (!$appointment) {
            return response()->json(['success'=> false ,'error' => 'Appointment not found'], 404);
        }

        // Validate session_date matches appointment_date
        if ($request->input('session_date') !== $appointment->appointment_date) {
            return response()->json(['success'=> false ,'error' => 'Session date must match appointment date'], 422);
        }

        // Create a new Session instance and link it to the existing appointment
        $session = new Session();
        $session->appointment_id = $request->input('appointment_id');
        $session->session_date = $request->input('session_date'); // Now validated
        $session->session_fees = $request->input('session_fees');
        $session->session_time = $request->input('session_time');
        $session->session_type = $request->input('session_type');
        $session->save();

        return response()->json(['success' => true,'message' => 'Session created successfully', 'session data' => $session]);
    }
}

// namespace App\Http\Controllers;

// use App\Models\Session;
// use App\Models\Appointment;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Validator;

// class SessionController extends Controller
// {
//     public function index()
//     {
//         $appointments = DB::table('doctor_appointment')->distinct()->pluck('appointment_id');

//         $sessionData = [];
//         foreach ($appointments as $appointment_id) {
//             $sessions = Session::where('appointment_id', $appointment_id)->get();

//             $doctors = DB::table('doctor_appointment')
//                 ->join('doctors', 'doctor_appointment.user_id', '=', 'doctors.user_id')
//                 ->join('users', 'doctors.user_id', '=', 'users.user_id')
//                 ->where('doctor_appointment.appointment_id', $appointment_id)
//                 ->select('doctors.*', 'users.first_name', 'users.last_name')
//                 ->get();

//             foreach ($sessions as $session) {
//                 $sessionData[] = [
//                     'session' => $session,
//                     'doctors' => $doctors->where('session_id', $session->id)->values(),
//                 ];
//             }
//         }

//         return response()->json([
//             'success' => true,
//             'sessions_with_doctors' => $sessionData,
//         ]);
//     }

//     /* -------------------------------------------------------------------------- */
//     public function store(Request $request)
//     {
//         $validator = Validator::make($request->all(), [
//             'appointment_id' => 'required|exists:appointments,appointment_id',
//             'session_date' => 'required|date',
//             'session_fees' => 'required|numeric',
//             'session_time' => 'required|date_format:H:i',
//             'session_type' => 'required|in:Therapy,Psychiatry,Physiatry,Prosthetics'
//         ]);

//         if ($validator->fails()) {
//             return response()->json(['success'=> false ,'error' => $validator->errors()], 422);
//         }

//         // Find the existing appointment
//         $appointment = Appointment::find($request->input('appointment_id'));

//         // Check if appointment exists (optional)
//         if (!$appointment) {
//             return response()->json(['success'=> false ,'error' => 'Appointment not found'], 404);
//         }

//         // Validate session_date matches appointment_date
//         if ($request->input('session_date') !== $appointment->appointment_date) {
//             return response()->json(['success'=> false ,'error' => 'Session date must match appointment date'], 422);
//         }

//         // Create a new Session instance and link it to the existing appointment
//         $session = new Session();
//         $session->appointment_id = $request->input('appointment_id');
//         $session->session_date = $request->input('session_date'); // Now validated
//         $session->session_fees = $request->input('session_fees');
//         $session->session_time = $request->input('session_time');
//         $session->session_type = $request->input('session_type');
//         $session->save();



//         return response()->json(['success' => true,'message' => 'Session created successfully', 'session data' => $session]);
//     }


// }
