<?php

namespace App\Http\Controllers;

use App\Models\Approvals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ApprovalController extends Controller
{
    /**
     * List
     *
     * Endpoint to list all requests from the logged in user for approval. Status reporting is not mandatory.
     *
     * @response Approvals
     */
    public function index(Request $request)
    {
        /**
         * Status 'pending' 'approved' 'rejected' 'canceled'
         * @example 'pending'
         * 
         */
        $request->string('status');

        if (!$request->has('status')) {
            return Approvals::where('user_id', Auth::id())->get();
        }

        $approvals = Approvals::where('user_id', Auth::id())
            ->where('status', $request->status) // Adiciona a condição de status
            ->get();

        return response()->json($approvals, 200);
    }


    /**
     * Approve
     *
     * Endpoint to approve the request. It is not necessary to inform the 'approved' status
     *
     * @param integer $id The approval item being updated.
     * 
     * @response Approvals
     */
    public function approve(int $id)
    {
        $approval = Approvals::find($id);

        if ($approval->user_id != Auth::id()) {
            return response()->json(['message' => 'User does not have permission to approve this request.'], 400);
        }

        if ($approval->status === 'approved') {
            return response()->json(['message' => 'This request has already been approved.'], 400);
        }

        if ($approval->request->status != 'pending') {
            return response()->json(['message' => 'This request is finalized and cannot be changed.'], 400);
        }

        // Verifica se a aprovação anterior foi concluída
        if ($this->canApprove($approval)) {
            $approval->update(['status' => 'approved']);

            //esse if retorna true se todas as aprovações da solicitação tiverem o status 'approved', e false caso contrário.
            if ($approval->request->approvals->every(fn($a) => $a->status == 'approved')) {
                $approval->request->update(['status' => 'approved']);
            }
            return response()->json($approval, 200);
        } else {
            return response()->json(['error' => 'The previous approval has not yet been completed.'], 400);
        }
    }

    /**
     * Reject
     *
     * Endpoint to reject the request. It is not necessary to inform the 'rejected' status
     *
     * @param integer $id The approval item being updated.
     * 
     * 
     */
    public function reject(int $id)
    {
        $approval = Approvals::find($id);

        if ($approval->user_id != Auth::id()) {
            return response()->json(['message' => 'User does not have permission to reject this request.'], 400);
        }

        if ($approval->status === 'rejected') {
            return response()->json(['message' => 'This request has already been rejected.'], 400);
        }

        if ($approval->request->status != 'pending') {
            return response()->json(['message' => 'This request is finalized and cannot be changed.'], 400);
        }

        if ($this->canApprove($approval)) {
            $approval->update(['status' => 'rejected']);

            $approval->request->update(['status' => 'rejected']);

            $this->cancelPendingApprovals($approval->request_id);

            return response()->json(['message' => 'Request rejected and sent back to the requester.']);
        } else {
            return response()->json(['error' => 'The previous approval has not yet been completed.'], 400);
        }
    }

    private function cancelPendingApprovals($requestId)
    {
        Approvals::where('request_id', $requestId)
            ->where('status', 'pending')
            ->update(['status' => 'canceled']);
    }

    private function canApprove($approval)
    {
        $previousOrder = $this->getPreviousOrder($approval->order);
        if ($previousOrder) {
            $previousApproval = Approvals::where('request_id', $approval->request_id)
                ->where('order', $previousOrder)
                ->first();
            return $previousApproval && $previousApproval->status == 'approved';
        }
        return true; // Se não houver nível anterior, pode aprovar
    }

    private function getPreviousOrder($currentOrder)
    {
        $orders = ['1', '2', '3', '4', '5'];
        $currentIndex = array_search($currentOrder, $orders);
        return $currentIndex > 0 ? $orders[$currentIndex - 1] : null;
    }
}
