<?php

namespace App\Http\Controllers;

use App\Models\Approvals;
use App\Models\Request as ModelsRequest;
use App\Models\User;
use App\Services\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    protected $approvalService;

    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * List
     *
     * Endpoint to list all request from the logged in user.
     *
     * @response ModelsRequest
     */
    public function index()
    {
        $requests = ModelsRequest::where('user_id', Auth::id())
            ->with('approvals')
            ->get();

        return response()->json($requests, 200);
    }

    /**
     * Create
     *
     * Endpoint to create a request for the logged in user. Generating pending items for approvals.
     *
     * @response ModelsRequest
     */
    public function store(Request $req)
    {
        $req->validate([
            /**
             * @var string{}
             * @example "15000.00"
             */
            'amount' => 'required|decimal:2',
        ]);

        DB::beginTransaction();

        $user = Auth::user();

        if ($user->role != 'requester') {
            return response()->json(['message' => 'User does not have permission to create request.'], 400);
        }

        try {
            $request = ModelsRequest::create(array_merge(
                $req->all(),
                ['status' => 'pending'],
                ['user_id' => $user->id]
            ));

            $this->createApprovals($request);

            DB::commit();

            return response()->json($request, 201);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => 'Error creating request'], 500);
        }
    }

    private function createApprovals($request)
    {
        $approvers = $this->approvalService->getApprovers($request->amount);
        $order = 1;

        foreach ($approvers as $approver) {
            Approvals::create([
                'request_id' => $request->id,
                'user_id' => $approver->id,
                'order' => $order,
                'status' => 'pending',
            ]);
            $order++;
        }
    }
}
