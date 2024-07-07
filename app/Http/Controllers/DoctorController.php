<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = DB::table('doctors')->get();
        return response()->json(['success' => true, 'doctors' => $doctors, 'count' => count($doctors)]);
    }


    public function showReservedParents(Request $request)
    {
        $user_id = $request->header('user_id');


        $doctorAppointments = DB::table('doctor_appointment')
            ->where('user_id', $user_id)
            ->pluck('appointment_id');

        if ($doctorAppointments->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'No appointments made'], 404);
        }

        $parents = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.user_id')
            ->whereIn('sessions.appointment_id', $doctorAppointments)
            ->select(
                'users.user_id',
                'users.first_name',
                'users.last_name',
                'users.marital_status',
                'users.date_of_birth',
                'sessions.session_type',
                'sessions.session_time',
                'sessions.session_date'
            )
            ->get();

        $userIDs = $parents->pluck('user_id');

        $children = DB::table('childrens')
            ->whereIn('user_id', $userIDs)
            ->get();

        $response = [];
        foreach ($parents as $parent) {
            $responseItem = [
                'first_name' => $parent->first_name,
                'last_name' => $parent->last_name,
                'marital_status' => $parent->marital_status,
                'date_of_birth' => $parent->date_of_birth,
                'session_type' => $parent->session_type,
                'session_time' => $parent->session_time,
                'session_date' => $parent->session_date,
                'children' => [],
            ];

            foreach ($children as $child) {
                if ($child->user_id === $parent->user_id) {
                    $responseItem['children'][] = [
                        'name' => $child->name,
                        'gender' => $child->gender,
                        'age' => Carbon::parse($child->date_of_birth)->age,
                    ];
                }
            }

            $response[] = $responseItem;
        }

        return response()->json(['success' => true, 'Patient list' => $response]);
    }

    public function allSessions(Request $request)
    {
        $user_id = $request->header('user_id');

        $doctorAppointments = DB::table('doctor_appointment')
            ->where('user_id', $user_id)
            ->pluck('appointment_id');

        if ($doctorAppointments->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'No appointments made'], 404);
        }

        $sessions = DB::table('sessions')
            ->whereIn('sessions.appointment_id', $doctorAppointments)
            ->select(
                'sessions.session_id',
                'sessions.session_type',
                'sessions.session_fees',
                'sessions.session_time',
                'sessions.session_date',
                'sessions.status'
            )
            ->get();

        return response()->json(['success' => true, 'Doctor Sessions list' => $sessions , 'count' => count($sessions)]);
    }

    public function doctorRequest(Request $request)
    {

        $user_id = $request->header('user_id');

        $validator = Validator::make($request->all(), [
            'bank_account_number' => 'required',
            'card_number' => 'required',
            'session_id' => 'required | exists:sessions,session_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        $session = DB::table('doctor_payment')->where('session_id', $request->input('session_id'))->first();

        if ($session) {
            return response()->json(['success' => false, 'error' => 'Request already sent'], 422);
        }

        DB::table('doctor_payment')->insert([
            'user_id' => $user_id,
            'bank_account_number' => $request->input('bank_account_number'),
            'card_number' => $request->input('card_number'),
            'doctor_request_status' => 'sent',
            'session_id' => $request->input('session_id')
        ]);

        return response()->json(['success' => true, 'message' => 'Request sent successfully']);
    }

    public function allDoctorRequests(Request $request)
    {
        $user_id = $request->header('user_id');

        // Check if user_id is provided in the request headers
        if (!$user_id) {
            return response()->json(['success' => false, 'message' => 'User ID is required'], 400);
        }

        // Retrieve the doctor's payment requests and include the doctor's name
        $requests = DB::table('doctor_payment')
            ->join('users', 'doctor_payment.user_id', '=', 'users.user_id')
            ->where('doctor_payment.user_id', $user_id)
            ->select(
                'doctor_payment.user_id',
                'doctor_payment.bank_account_number',
                'doctor_payment.card_number',
                'doctor_payment.doctor_request_status',
                'doctor_payment.session_id',
                'users.first_name',
                'users.last_name'
            )
            ->get();

        return response()->json(['success' => true, 'doctor_requests' => $requests , 'count' => count($requests)]);
    }


    public function allDoctorsRequests(Request $request)
    {
        $user_id = $request->header('user_id');

        $requests = DB::table('doctor_payment')
            ->join('users', 'doctor_payment.user_id', '=', 'users.user_id')
            ->select('doctor_payment.user_id', 'doctor_payment.bank_account_number', 'doctor_payment.card_number', 'doctor_payment.doctor_request_status', 'doctor_payment.session_id', 'users.first_name', 'users.last_name')
            ->get();

        return response()->json(['success' => true, 'All_doctors_requests' => $requests]);
    }

    public function updateDoctorsRequest(Request $request)
    {
        $user_id = $request->header('user_id');
        $session_id = $request->input('session_id');
        $new_status = $request->input('doctor_request_status');

        // Validate input
        if (!$session_id || !$new_status) {
            return response()->json(['success' => false, 'error' => 'Session ID and doctor request status are required'], 400);
        }

        // Retrieve the doctor's request
        $doctorRequest = DB::table('doctor_payment')->where('session_id', $session_id)->first();

        if (!$doctorRequest) {
            return response()->json(['success' => false, 'error' => 'Request not found'], 404);
        }

        // Update the doctor's request status
        $updated = DB::table('doctor_payment')
            ->where('session_id', $session_id)
            ->update(['doctor_request_status' => $new_status]);

        if ($updated) {
            return response()->json(['success' => true, 'message' => 'Request updated successfully']);
        } else {
            return response()->json(['success' => false, 'error' => 'Failed to update request'], 500);
        }
    }
}
