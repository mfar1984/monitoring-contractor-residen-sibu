<?php

namespace App\Services;

use App\Models\ContractorAnalysisTransfer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ContractorAnalysisTransferService
{
    public function createTransfer(array $data, UploadedFile $file, User $user): ContractorAnalysisTransfer
    {
        DB::beginTransaction();
        
        try {
            // Store attachment file
            $attachmentPath = $this->storeAttachment($file);
            
            // Create transfer record
            $transfer = ContractorAnalysisTransfer::create([
                'transfer_number' => ContractorAnalysisTransfer::generateTransferNumber(),
                'created_by' => $user->id,
                'agency_category_id' => $user->agency_category_id ?? $data['agency_category_id'] ?? null,
                'attachment_path' => $attachmentPath,
                'status' => 'Draft',
            ]);
            
            // Attach projects to transfer
            $transfer->projects()->attach($data['project_ids']);
            
            // Update project statuses
            Project::whereIn('id', $data['project_ids'])
                ->update(['status' => 'Analysis Pending']);
            
            DB::commit();
            
            return $transfer;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded file if transaction fails
            if (isset($attachmentPath)) {
                Storage::delete($attachmentPath);
            }
            
            throw $e;
        }
    }

    public function deleteTransfer(ContractorAnalysisTransfer $transfer): bool
    {
        if ($transfer->status !== 'Draft') {
            throw new \Exception('Only draft transfers can be deleted');
        }
        
        DB::beginTransaction();
        
        try {
            // Get project IDs before deletion
            $projectIds = $transfer->projects()->pluck('project_id')->toArray();
            
            // Rollback project statuses (assuming previous status was 'Active')
            Project::whereIn('id', $projectIds)
                ->update(['status' => 'Active']);
            
            // Delete attachment file
            if ($transfer->attachment_path) {
                Storage::delete($transfer->attachment_path);
            }
            
            // Delete transfer (cascade will delete pivot entries)
            $transfer->delete();
            
            DB::commit();
            
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function storeAttachment(UploadedFile $file): string
    {
        $filename = time() . '_' . $file->getClientOriginalName();
        return $file->storeAs('contractor-analysis-attachments', $filename, 'public');
    }

    public function downloadAttachment(ContractorAnalysisTransfer $transfer, User $user)
    {
        // Verify user has permission to access this transfer
        if (!$user->residen_category_id && $user->agency_category_id !== $transfer->agency_category_id) {
            throw new \Exception('Unauthorized access to attachment');
        }
        
        return Storage::disk('public')->download($transfer->attachment_path);
    }
}
