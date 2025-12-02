<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketStoreRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    //
    public function store(TicketStoreRequest $request)
    {
        $data = $request->validated();
        DB::beginTransaction();

        try {
            $ticket = new Ticket;
            $ticket->user_id = auth()->user()->id;
            $ticket->code = 'TIC-' . rand(1000, 99999);
            $ticket->title = $data['title'];
            $ticket->status = 'open';
            $ticket->description = $data['description'];
            $ticket->priority = $data['priority'];

            $ticket->save();

            DB::commit();

            return response()->json([
                'message' => 'Ticket berhasil ditambahkan',
                'data' => new TicketResource($ticket),
            ]);
        } catch (Exception $th) {
            //throw $th;
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi Kesalahan',
                'data' => null,
                'error'=>$th->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $query = Ticket::query();
            $query->orderBy('created_at', 'desc');

            if ($request->search) {
                $query->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('title', 'like', '%' . $request->search . '%');
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            if ($request->priority) {
                $query->where('priority', $request->priority);
            }

            if (auth()->user()->role == 'user') {
                $query->where('user_id', auth()->user()->id);
            }

            $tickets = $query->get();

            return response()->json([
                'message' => 'Data tiket berhasil ditampilkan',
                'data' => TicketResource::collection($tickets)
            ]);
        } catch (\Exception $th) {
            return response()->json([
                'message' => 'Terjadi Kesalahan',
                'data' => null,
            ], 500);
        }
    }
}
