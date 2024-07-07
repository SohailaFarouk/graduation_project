<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::get();
        return response()->json(['success'=> true ,'events' => $events ,'count' => count($events)]);
    }


    /* -------------------------------------------------------------------------- */

    public function store(Request $request)
    {
        $user_id = $request->header('user_id');

        $validator = Validator::make($request->all(), [
            'event_name' => 'required|string|max:255',
            'event_description' => 'required|string',
            'event_price' => 'required|numeric|min:0',
            'event_location' => 'required|string',
            'start_date' => 'required',
            'end_date' => 'required',
            'number_of_tickets' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'image' => 'nullable|image',
            'event_status' => 'required|string|in:Ongoing,Upcoming,Completed',
        ]);

        if ($validator->fails()) {
            return response()->json(['success'=> false ,'errors' => $validator->errors()], 422);
        }

        $event = new Event();
        $event->event_name = $request->input('event_name');
        $event->event_description = $request->input('event_description');
        $event->event_price = $request->input('event_price');
        $event->event_location = $request->input('event_location');
        $event->start_date = $request->input('start_date');
        $event->end_date = $request->input('end_date');
        $event->event_status = $request->input('event_status');
        $event->start_time = $request->input('start_time');
        $event->end_time = $request->input('end_time');
        $event->number_of_tickets = $request->input('number_of_tickets');

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('event_images', 'public');
            $event->image = $imagePath;
        }

        $event->save();

        $admin = DB::table('admins')
        ->where('user_id', $user_id);
        if ($admin) {
            DB::table('admins')->where('user_id', $user_id)
            ->update(['event_id' => $event->event_id]);
        }

        return response()->json(['success' => true,'message' => 'event created successfully', 'event' => $event], 201);
    }

    /* -------------------------------------------------------------------------- */
    public function show(request $request)
    {
        $event_id = $request->input('event_id');
        $event = Event::find($event_id);
        if ($event == null) {
            return response()->json(['success'=> false ,"message" => "event not found"], 404);
        }
        return response()->json(['success' => true,"event" => $event]);
    }

    /* -------------------------------------------------------------------------- */
    public function edit(string $event_id)
    {
        $event = Event::findOrFail($event_id);
        return response()->json(['success' => true,"event" => $event]);
    }



    /* -------------------------------------------------------------------------- */
    public function update(Request $request)
    {

        $event_id = $request->input('event_id');
        $event = Event::find($event_id);
        if (!$event) {
            return response()->json(['success'=> false ,'error' => 'event not found'], 404);
        }

        if ($request->filled('event_name')) {
            $event->event_name = $request->input('event_name');
        }
        if ($request->filled('event_description')) {
            $event->event_description = $request->input('event_description');
        }
        if ($request->filled('event_price')) {
            $event->event_price = $request->input('event_price');
        }
        if ($request->filled('event_location')) {
            $event->event_location = $request->input('event_location');
        }
        if ($request->filled('start_date')) {
            $event->start_date = $request->input('start_date');
        }
        if ($request->filled('end_date')) {
            $event->end_date = $request->input('end_date');
        }
        if ($request->filled('event_status')) {
            $event->event_status = $request->input('event_status');
        }
        if ($request->filled('start_time')) {
            $event->start_time = $request->input('start_time');
        }
        if ($request->filled('end_time')) {
            $event->end_time = $request->input('end_time');
        }
        if ($request->filled('number_of_tickets')) {
            $event->number_of_tickets = $request->input('number_of_tickets');
        }
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('event_images', 'public');
            $event->image = $imagePath;
        }

        $event->save();

        return response()->json(['success' => true,'message' => 'event updated successfully', 'event' => $event]);
    }

    /* -------------------------------------------------------------------------- */
    public function destroy(Request $request)
    {
        $event_id = $request->input('event_id');
        $event = Event::find($event_id);

        if (!$event) {
            return response()->json(['success'=> false ,'error' => 'event not found'], 404);
        }

        $event->delete();
        DB::statement('ALTER TABLE events AUTO_INCREMENT = 1');

        return response()->json(['message' => 'event deleted successfully']);
    }

}
