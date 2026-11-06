<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    protected $fillable = ['employee_id', 'type', 'name', 'file_path', 'uploaded_by'];

    public const TYPES = [
        'piece_identite' => "Carte d'identité",
        'passeport'       => 'Passeport',
        'diplome'        => 'Diplôme',
        'contrat'        => 'Contrat',
        'autre'          => 'Autre',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
