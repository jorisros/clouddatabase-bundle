<?php

namespace App\Service;

use App\Entity\Tenant;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class GoogleBigQueryClient
{
    private string $tokenUrl = 'https://oauth2.googleapis.com/token';
    private string $scopes = 'https://www.googleapis.com/auth/bigquery';

    private Client $httpClient;
    private string $token;

    public function __construct(private Tenant $tenant) {
        $this->httpClient = new Client();
        $this->token = $this->getToken();
    }

    public function getModels(string $projectId, string $dataset, string $table): array
    {
        $url = "https://bigquery.googleapis.com/bigquery/v2/projects/$projectId/datasets/$dataset/tables/$table";

        try {
            $response = $this->httpClient->get($url, [
                'headers' => [
                    'Authorization' => "Bearer $this->token",
                ],
            ]);

            $content = json_decode($response->getBody(), true);
            return $content['schema']['fields'];
        } catch (RequestException $e) {
            throw new \Exception("Failed to fetch model: " . $e->getMessage());
        }
        return [];
    }

    public function listTables(string $projectId, string $dataset): array
    {
        $url = "https://bigquery.googleapis.com/bigquery/v2/projects/$projectId/datasets/$dataset/tables";

        try {
            $response = $this->httpClient->get($url, [
                'headers' => [
                    'Authorization' => "Bearer $this->token",
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new \Exception("Failed to fetch tables: " . $e->getMessage());
        }
    }
    public function listDatasets(string $projectId): array
    {
        $url = "https://bigquery.googleapis.com/bigquery/v2/projects/$projectId/datasets";

        try {
            $response = $this->httpClient->get($url, [
                'headers' => [
                    'Authorization' => "Bearer $this->token",
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new \Exception("Failed to fetch datasets: " . $e->getMessage());
        }
    }

    private function getToken(): string
    {
        try {
            $response = $this->httpClient->post($this->tokenUrl, [
                'form_params' => [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $this->getJWTEncodedToken(),
                ],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);
            $content = json_decode($response->getBody()->getContents(), true);
            return $content['access_token'];
        } catch (RequestException $e) {
            throw new \Exception("Failed to fetch auth token: " . $e->getMessage());
        }
    }

    private function getJWTEncodedToken(): string
    {
        $serviceAccount = json_decode($this->tenant->getGoogleServiceAccount(), true);

        if (!$serviceAccount || !isset($serviceAccount['private_key'], $serviceAccount['client_email'])) {
            throw new \Exception("Invalid service account json. Goto settings to upload it");
        }

        // Extract required fields
        $privateKey = $serviceAccount['private_key'];
        $clientEmail = $serviceAccount['client_email'];

        // Create JWT claim set
        $now = time();
        return JWT::encode([
            'iss' => $clientEmail,          // Issuer (service account email)
            'sub' => $clientEmail,          // Subject (service account email)
            'aud' => $this->tokenUrl,       // Audience (token endpoint)
            'scope' => $this->scopes,       // Scopes
            'iat' => $now,                  // Issued at
            'exp' => $now + 3600            // Expiration time (1 hour)
        ], $privateKey, 'RS256');
    }

}
