<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class D1ServiceUnavailableException extends Exception
{
    protected $message = 'Layanan eksternal D-1 tidak dapat dihubungi';
}

class D1ProjectServiceClient
{
    /**
     * Data mock internal untuk cadangan jika server D-1 mati/belum diset.
     */
    protected array $mockProjects = [
        'prj-demo-001' => [
            'projectId' => 'prj-demo-001',
            'projectName' => 'Implementasi ERP Komersial',
            'poReceived' => true,
            'poStatus' => 'PO_ADA',
            'isTemporaryClient' => false,
            'clientId' => 'cli-demo-001',
            'status' => 'ACTIVE',
        ],
    ];

    /**
     * Mengambil informasi proyek dari Modul D-1 via HTTP Client.
     */
    public function getProject(string $projectId): ?array
    {
        // Skenario simulasi kegagalan koneksi eksternal D-1
        if ($projectId === 'prj-d1-down' || request()->header('X-Simulate-D1-Down') === 'true') {
            throw new D1ServiceUnavailableException('Layanan eksternal D-1 tidak dapat dihubungi');
        }

        // Ambil base URL dari config atau .env
        $baseUrl = config('services.modul_d1.base_url', env('D1_SERVICE_BASE_URL', 'http://localhost:3000/api/v1'));
        
        // Ambil token yang sudah kitauji sebelumnya
        $token = env('API_DUMMY_TOKEN', 'dummy-token-lokal');

        if (!empty($baseUrl)) {
            try {
                // Melakukan request HTTP ke server D-1 dengan menyertakan token
                $response = Http::timeout(3)
                    ->withToken($token)
                    ->get("{$baseUrl}/projects/{$projectId}");

                if ($response->status() === 404) {
                    return null;
                }

                Log::info('RESPONS D-1:', $response->json());

                if ($response->failed()) {
                    throw new D1ServiceUnavailableException('Layanan eksternal D-1 mengalami gangguan HTTP ' . $response->status());
                }

                $data = $response->json('data') ?? $response->json();
                $poReceived = (bool) ($data['poReceived'] ?? false);

                // Memetakan respons agar sesuai dengan kebutuhan Modul D-2
                return [
                    'projectId' => $data['projectId'] ?? $projectId,
                    'projectName' => $data['projectName'] ?? 'Proyek D-1',
                    'poReceived' => $poReceived,
                    'poStatus' => $poReceived ? 'PO_ADA' : 'PO_TIDAK_ADA',
                    'isTemporaryClient' => (bool) ($data['isTemporaryClient'] ?? false),
                    'clientId' => $data['clientId'] ?? null,
                    'status' => $data['status'] ?? 'ACTIVE',
                ];

            } catch (Exception $e) {
                if ($e instanceof D1ServiceUnavailableException) {
                    throw $e;
                }
                Log::warning("Gagal menghubungi layanan D-1 pada {$baseUrl}: " . $e->getMessage());
                throw new D1ServiceUnavailableException('Layanan eksternal D-1 tidak dapat dihubungi: ' . $e->getMessage());
            }
        }

        // Fallback ke mock jika URL tidak ada
        if (isset($this->mockProjects[$projectId])) {
            return $this->mockProjects[$projectId];
        }

        return null;
    }
}
