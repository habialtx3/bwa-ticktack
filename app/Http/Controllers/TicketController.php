<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketReplyRequest;
use App\Http\Requests\TicketStoreRequest;
use App\Http\Resources\TicketReplyResource;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\TicketReply;
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
                'error' => $th->getMessage(),
            ], 500);
        }
    }


    public function show($code)
    {
        try {
            $ticket = Ticket::where('code', $code)->first();
            if (!$ticket) {
                return response()->json([
                    'message' => 'ticket not found'
                ], 404);
            }

            if (auth()->user()->role == 'user' && $ticket->user_id != auth()->user()->id) {
                return response()->json([
                    'message' => 'Permission Denied'
                ], 404);
            }

            return response()->json([
                'message' => 'ticket reply has successfully showed',
                'data' => new TicketResource($ticket),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Terjadi Kesalahan',
                'data' => null,
                'error' => $th->getMessage(),
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
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function storeReply(TicketReplyRequest $request, $code)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $ticket = Ticket::where('code', $code)->first();
            
            if (!$ticket) {
                return response()->json([
                    'message' => 'ticket not found'
                ], 404);
            }

            if (auth()->user()->role == 'user' && $ticket->user_id != auth()->user()->id) {
                return response()->json([
                    'message' => 'Permission Denied'
                ], 404);
            }

            $ticketReply = new TicketReply();
            $ticketReply->ticket_id = $ticket->id;
            $ticketReply->user_id = auth()->user()->id;
            $ticketReply->content = $data['content'];
            $ticketReply->save();

            if(auth()->user()->role == 'admin'){
                $ticket->status = $data['status'];
                if($data['status']=='resolved'){
                    $ticket->completedAt = \now();
                }
                $ticket->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Ticket reply created success',
                'data' => new TicketReplyResource($ticketReply)
            ]);

        } catch (Exception $th) {
            //throw $th;
            return response()->json([
                'message' => 'Terjadi Kesalahan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
