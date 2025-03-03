<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class PasswordGeneratorService
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(HttpClientInterface $client, string $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    public function generatePassword(int $length = 12, string $characters = 'all'): string
    {
        try {
            $response = $this->client->request('GET', 'https://api.api-ninjas.com/v1/passwordgenerator', [
                'headers' => [
                    'X-Api-Key' => $this->apiKey,  // API key from constructor
                ],
                'query' => [
                    'length' => $length,
                    'characters' => $characters,
                ],
            ]);
    
            $statusCode = $response->getStatusCode();
         
            $data = $response->toArray();
          
    
            if ($statusCode !== 200) {
                throw new \Exception("API Error: HTTP $statusCode");
            }
   
            return $data['random_password'] ?? 'Error: No password in response';
    
        } catch (\Exception $e) {
            // Log error message (if you have Monolog configured)
            error_log("Password API Error: " . $e->getMessage());
    
            return "Error: " . $e->getMessage(); // Return actual error for debugging
        }
    }
    
}
