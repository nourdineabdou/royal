<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Employee;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'must_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    /** Caractères permis : lettres sans accent, chiffres, point, tiret, souligné ; 3 à 30. */
    public const USERNAME_RULE = 'regex:/^[a-z0-9._-]{3,30}$/';

    protected static function booted(): void
    {
        // Par défaut, le nom d'utilisateur vient du nom
        static::creating(function (User $user) {
            $user->username = $user->username
                ? static::normalizeUsername($user->username)
                : static::uniqueUsernameFrom($user->name);
        });
    }

    /** « Abdel Rahim » → « abdel.rahim » ; « 0042 » → « 0042 ». Minuscules, sans accent, espaces → point. */
    public static function normalizeUsername(?string $value): string
    {
        $v = strtolower(\Illuminate\Support\Str::ascii(trim((string) $value)));

        return preg_replace('/\s+/', '.', $v);
    }

    /** Nom d'utilisateur libre tiré du nom (« sambou », puis « sambou2 »… en cas de doublon). */
    public static function uniqueUsernameFrom(?string $name, ?int $exceptId = null): string
    {
        $base = trim(preg_replace('/[^a-z0-9._-]/', '', static::normalizeUsername($name)), '.-_');
        $base = substr($base !== '' ? $base : 'utilisateur', 0, 26);
        $base = str_pad($base, 3, '0');

        $candidate = $base;
        for ($i = 2; static::query()->where('username', $candidate)->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))->exists(); $i++) {
            $candidate = $base . $i;
        }

        return $candidate;
    }

    /** Retrouve l'utilisateur par nom d'utilisateur ou par e-mail. */
    public static function findForLogin(string $login): ?self
    {
        $login = trim($login);
        if (str_contains($login, '@')) {
            return static::where('email', $login)->first();
        }

        return static::where('username', static::normalizeUsername($login))->first();
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
}
