<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends Controller
{
    public function index()
    {
        // Fetch feedback with order details and user information
        $feedbacks = DB::table('feedbacks')
            ->join('orders', 'feedbacks.order_id', '=', 'orders.order_id')
            ->join('users', 'orders.user_id', '=', 'users.user_id')
            ->select(
                'feedbacks.feedback_id',
                'feedbacks.order_id',
                'feedbacks.feedback_content',
                'feedbacks.rating',
                'orders.order_details',
                'users.user_id',
                'users.first_name',
                'users.last_name',
            )
            ->get();

        // Decode order_details for each feedback
        $feedbacks = $feedbacks->map(function ($feedback) {
            $feedback->order_details = json_decode($feedback->order_details);
            return $feedback;
        });

        return response()->json(['success' => true, 'feedback' => $feedbacks, 'count' => count($feedbacks)]);
    }

    /* -------------------------------------------------------------------------- */
    public function show(Request $request)
    {
        $user_id = $request->header('user_id');
        $validator = Validator::make($request->all(), [
            'feedback_id' => 'required|exists:feedbacks,feedback_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        $feedbackId = $request->input('feedback_id');
        $feedback = Feedback::find($feedbackId);

        if ($feedback == null) {
            return response()->json(['success' => false, "error" => "Feedback not found"], 404);
        }

        // Insert into admin_feedback table
        DB::table('admin_feedback')->insert([
            'feedback_id' => $feedbackId,
            'user_id' => $user_id,
        ]);

        return response()->json(['success' => true, "feedback" => $feedback]);
    }
    /* -------------------------------------------------------------------------- */
    public function makeFeedback(Request $request)
    {
        $user_id = $request->header('user_id');

        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,order_id',
            'feedback_content' => 'required',
            'rating' => 'required|numeric|min:1|max:5',
        ]);


        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }
        $feedbackId = DB::table('feedbacks')->insertGetId([
            'order_id' => $request->input('order_id'),
            'feedback_content' => $request->input('feedback_content'),
            'rating' => $request->input('rating'),
        ]);

        DB::table('parent_feedback')->insert([
            'feedback_id' => $feedbackId,
            'user_id' => $user_id,
        ]);

        // Return a success response
        return response()->json(['success' => true, 'message' => 'Thanks for your feedback'], 200);
    }
}
