<?php

namespace App\Services;

use App\Models\AuditEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditService
{
    /**
     * Record an audit event in the append-only audit log.
     * Never stores passwords, tokens, or plain credentials.
     *
     * @param  array<string, mixed>|null  $changes
     */
    public function log(
        string $action,
        string $outcome,
        string $resourceType,
        ?string $resourceReference = null,
        ?array $changes = null,
        ?string $description = null,
        ?int $facilityId = null,
        ?int $actorId = null,
        ?string $correlationId = null
    ): AuditEvent {
        $actorId ??= Auth::id();
        $correlationId ??= request()->attributes->get('correlation_id') ?? (string) Str::uuid();

        $sanitizedChanges = null;
        if ($changes !== null) {
            $sanitizedChanges = $this->sanitizeChanges($changes);
        }

        $auditRef = 'AUD-'.Str::uuid();

        return AuditEvent::create([
            'audit_ref' => $auditRef,
            'actor_id' => $actorId,
            'facility_id' => $facilityId,
            'occurred_at' => now(),
            'recorded_at' => now(),
            'action' => $action,
            'outcome' => $outcome,
            'resource_type' => $resourceType,
            'resource_reference' => $resourceReference,
            'correlation_id' => $correlationId,
            'description' => $description,
            'changes' => $sanitizedChanges,
        ]);
    }

    /**
     * Filter out password, token, and secret fields.
     *
     * @param  array<string, mixed>  $changes
     * @return array<string, mixed>
     */
    private function sanitizeChanges(array $changes): array
    {
        $sensitiveKeys = ['password', 'password_confirmation', 'initial_password', 'remember_token', 'token', 'access_token', 'refresh_token', 'api_key', 'authorization', 'client_secret', 'secret', 'pin', '_token', 'id_number', 'identity_number', 'phone', 'email', 'residence', 'kin_contact'];
        $clean = [];

        foreach ($changes as $key => $value) {
            if (in_array(strtolower($key), $sensitiveKeys, true)) {
                $clean[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $clean[$key] = $this->sanitizeChanges($value);
            } else {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }

    /** @param array<string, mixed> $changes
     * @return array<string, mixed>
     */
    public function redact(array $changes): array
    {
        return $this->sanitizeChanges($changes);
    }

    public static function signatureFor(AuditEvent $event): string
    {
        $payload = $event->only(['audit_ref', 'actor_id', 'facility_id', 'action', 'outcome', 'resource_type', 'resource_reference', 'correlation_id', 'description', 'changes']);
        $payload['actor_id'] = $payload['actor_id'] === null ? null : (int) $payload['actor_id'];
        $payload['facility_id'] = $payload['facility_id'] === null ? null : (int) $payload['facility_id'];
        $payload['occurred_at'] = $event->occurred_at?->format('Y-m-d H:i:s');
        $payload['recorded_at'] = $event->recorded_at?->format('Y-m-d H:i:s');
        $canonicalize = static function (array $data) use (&$canonicalize): array {
            if (! array_is_list($data)) {
                ksort($data);
            }
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = $canonicalize($value);
                }
            }

            return $data;
        };

        return hash_hmac('sha256', json_encode($canonicalize($payload), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), config('app.key'));
    }
}
