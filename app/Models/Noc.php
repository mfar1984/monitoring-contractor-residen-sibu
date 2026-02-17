<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Noc extends Model
{
    protected $fillable = [
        'noc_number',
        'parliament_id',
        'dun_id',
        'noc_date',
        'created_by',
        'status',
        'first_approver_id',
        'first_approved_at',
        'first_approval_remarks',
        'second_approver_id',
        'second_approved_at',
        'second_approval_remarks',
        'noc_letter_attachment',
        'noc_project_list_attachment',
    ];

    protected $casts = [
        'noc_date' => 'date',
        'first_approved_at' => 'datetime',
        'second_approved_at' => 'datetime',
    ];

    /**
     * Get the parliament
     */
    public function parliament()
    {
        return $this->belongsTo(Parliament::class);
    }

    /**
     * Get the DUN
     */
    public function dun()
    {
        return $this->belongsTo(Dun::class);
    }

    /**
     * Get the user who created the NOC
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the first approver
     */
    public function firstApprover()
    {
        return $this->belongsTo(User::class, 'first_approver_id');
    }

    /**
     * Get the second approver
     */
    public function secondApprover()
    {
        return $this->belongsTo(User::class, 'second_approver_id');
    }

    /**
     * Get all projects in this NOC (only imported projects with project_id)
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'noc_project')
            ->withPivot([
                'tahun_rtp',
                'no_projek',
                'nama_projek_asal',
                'nama_projek_baru',
                'kos_asal',
                'kos_baru',
                'agensi_pelaksana_asal',
                'agensi_pelaksana_baru',
                'noc_note_id'
            ])
            ->withTimestamps();
    }

    /**
     * Get ALL project entries in this NOC (including new projects without project_id)
     * Returns collection of stdClass objects with pivot data
     */
    public function getAllProjectEntries()
    {
        return \DB::table('noc_project')
            ->where('noc_id', $this->id)
            ->get()
            ->map(function ($entry) {
                // Convert to object with pivot property for consistency with Eloquent relationships
                $obj = new \stdClass();
                $obj->pivot = $entry;
                return $obj;
            });
    }

    /**
     * Get available projects for NOC creation
     * Filters by user's parliament/DUN and excludes projects already in NOCs
     */
    public static function getAvailableProjects($user)
    {
        $query = Project::query();
        
        // Filter by user's parliament or DUN
        if ($user->parliament_id) {
            $query->where('parliament_id', $user->parliament_id);
        } elseif ($user->dun_id) {
            $query->where('dun_id', $user->dun_id);
        }
        
        // Only include Active projects (not in NOC status)
        $query->where('status', 'Active');
        
        // Exclude projects already in NOCs
        $projectsInNocs = \DB::table('noc_project')
            ->whereNotNull('project_id')
            ->pluck('project_id')
            ->toArray();
        
        if (!empty($projectsInNocs)) {
            $query->whereNotIn('id', $projectsInNocs);
        }
        
        return $query->with('agencyCategory')->get();
    }

    /**
     * Generate NOC number
     */
    public static function generateNocNumber()
    {
        $year = date('Y');
        $lastNoc = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastNoc ? intval(substr($lastNoc->noc_number, -3)) + 1 : 1;
        
        return 'NOC/' . $year . '/' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
