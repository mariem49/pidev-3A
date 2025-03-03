<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProfanityFilter
{
    private $client;
    private $apiKey;

    public function __construct(HttpClientInterface $client, string $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    public function checkProfanity(string $text): array
{
    try {
        $response = $this->client->request('GET', 'https://api.api-ninjas.com/v1/profanityfilter', [
            'headers' => [
                'X-Api-Key' => $this->apiKey,
            ],
            'query' => [
                'text' => $text,
            ],
        ]);

        $statusCode = $response->getStatusCode();
        $data = $response->toArray(); // Convert response to an array

        if ($statusCode !== 200) {
            throw new \Exception("API Error: HTTP $statusCode");
        }

        // ✅ Ensure the return type is always an array
        return [
            'has_profanity' => $data['has_profanity'] ?? false
        ];

    } catch (\Exception $e) {
        // Log the error
        error_log("Profanity API Error: " . $e->getMessage());

        // ✅ Return a structured array instead of just a boolean
        return [
            'error' => $e->getMessage(),
            'has_profanity' => false
        ];
    }
}
}