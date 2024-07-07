<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VoucherController extends Controller
{

    public function index()
    {
        $vouchers = Voucher::get();
        return response()->json(['success' => true,'vouchers' => $vouchers , 'count' => count($vouchers)]);
    }

    /* -------------------------------------------------------------------------- */
    public function store(Request $request)
    {
        $user_id = $request->header('user_id');

        $validator = Validator::make($request->all(), [
            'voucher_code' => 'required|string|max:255',
            'voucher_percentage' => 'required|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $voucher = new Voucher();
        $voucher->voucher_code = $request->input('voucher_code');
        $voucher->voucher_percentage = $request->input('voucher_percentage') / 100;

        $voucher->save();
        $voucher->admin()->attach($user_id);

        return response()->json(['success' => true,'message' => 'voucher created successfully', 'voucher' => $voucher], 201);
    }

    /* -------------------------------------------------------------------------- */
    public function show(request $request)
    {
        $voucher_id = $request->input('voucher_id');
        $voucher = Voucher::find($voucher_id);
        if ($voucher == null) {
            return response()->json(["message" => "voucher not found"], 404);
        }
        return response()->json(['success' => true,"voucher" => $voucher]);
    }

    /* -------------------------------------------------------------------------- */
    public function edit(string $voucher_id)
    {
        $voucher = Voucher::findOrFail($voucher_id);
        return response()->json(['success' => true,"voucher" => $voucher]);
    }

    /* -------------------------------------------------------------------------- */
    public function update(Request $request)
    {
        $voucher_id = $request->input('voucher_id');
        $voucher = Voucher::find($voucher_id);
        if (!$voucher) {
            return response()->json(['error' => 'voucher not found'], 404);
        }

        if ($request->filled('voucher_code')) {
            $voucher->voucher_code = $request->input('voucher_code');
        }
        if ($request->filled('voucher_percentage')) {
            $voucher->voucher_percentage = $request->input('voucher_percentage');
        }

        $voucher->save();

        return response()->json(['success' => true,'message' => 'voucher updated successfully', 'voucher' => $voucher]);
    }

    /* -------------------------------------------------------------------------- */
    public function destroy(Request $request)
    {
        $voucher_id = $request->input('voucher_id');
        $voucher = Voucher::find($voucher_id);

        if (!$voucher) {
            return response()->json(['success'=> false ,'error' => 'voucher not found'], 404);
        }

        $voucher->delete();
        DB::statement('ALTER TABLE vouchers AUTO_INCREMENT = 1');

        return response()->json(['success'=> true ,'message' => 'voucher deleted successfully']);
    }
}
