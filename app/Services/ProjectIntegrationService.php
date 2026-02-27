<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProjectIntegrationService
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl = config('services.project.url', env('API_PROJECT_URL', 'http://127.0.0.1:8001'));
        $this->token   = config('services.project.token', env('PROJECT_API_TOKEN', ''));
    }

    /**
     * Return a pre-configured HTTP client with Bearer token.
     */
    protected function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
                   ->withToken($this->token)
                   ->acceptJson();
    }

    /**
     * Search projects from the external Project System.
     */
    public function searchProjects($term = null): array
    {
        try {
            $response = $this->client()->get('/api/projects/search', [
                'term' => $term,
            ]);

            if ($response->successful()) {
                return $response->json('results') ?? [];
            }

            Log::error('Project API search failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return [];
        } catch (\Exception $e) {
            Log::error('Project API connection error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch unit requests from the Project System.
     *
     * @param  string|null $status  Optional status filter (e.g. 'FORWARDED_TO_WORKSHOP')
     * @return array
     */
    public function getUnitRequests(?string $status = null): array
    {
        try {
            $params   = $status ? ['status' => $status] : [];
            $response = $this->client()->get('/api/unit-requests', $params);

            if ($response->successful()) {
                $body = $response->json();

                // API may return a plain array OR { value: [...], Count: n }
                if (is_array($body) && array_is_list($body)) {
                    return $body;
                }
                return $body['value'] ?? $body['data'] ?? [];
            }

            Log::error('Project API unit-requests failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return [];
        } catch (\Exception $e) {
            Log::error('Project API connection error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a single project detail by ID.
     */
    public function getProject($id): array
    {
        return Cache::remember("project_detail_{$id}", 3600, function () use ($id) {
            try {
                $response = $this->client()->get("/api/projects/{$id}");

                if ($response->successful()) {
                    $data = $response->json();
                    // Normalise: ensure both 'id' and 'project_name' are present
                    return [
                        'id'           => $data['id'] ?? $id,
                        'project_name' => $data['project_name'] ?? $data['name'] ?? "Project #{$id}",
                    ];
                }

                Log::warning("Project API get failed for id={$id}", [
                    'status' => $response->status(),
                ]);
                return ['id' => $id, 'project_name' => "Project #{$id}"];
            } catch (\Exception $e) {
                Log::error('Project API getProject error: ' . $e->getMessage());
                return ['id' => $id, 'project_name' => "Project #{$id}"];
            }
        });
    }

    /**
     * Map multiple project IDs to their names.
     */
    public function getProjectNames(array $ids): array
    {
        $ids    = array_unique($ids);
        $result = [];
        foreach ($ids as $id) {
            $project      = $this->getProject($id);
            $result[$id]  = $project['project_name'] ?? "Project #{$id}";
        }
        return $result;
    }

    public function checkConnection(): bool
    {
        try {
            // Using unit-requests as a simpler health check if search fails
            $response = $this->client()->timeout(3)->get('/api/projects/search', ['limit' => 1]);
            if (!$response->successful()) {
                Log::warning("Project Server Health Check Failed. Status: " . $response->status());
            }
            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Project Server Connection Exception: " . $e->getMessage());
            return false;
        }
    }
}
