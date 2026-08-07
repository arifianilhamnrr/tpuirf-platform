<?php

namespace App\Application\Services;

use App\Application\Interfaces\RecommendationServiceInterface;
use App\Domain\DTOs\RecommendationDTO;
use App\Domain\Repositories\RecommendationRepositoryInterface;
use App\Domain\Repositories\UserProfileRepositoryInterface;
use App\Domain\Repositories\InteractionLogRepositoryInterface;
use Illuminate\Support\Facades\Http;

class RecommendationService implements RecommendationServiceInterface
{
    public function __construct(
        private readonly RecommendationRepositoryInterface $recommendationRepository,
        private readonly UserProfileRepositoryInterface $profileRepository,
        private readonly InteractionLogRepositoryInterface $logRepository,
    ) {}

    public function generateRecommendation(string $userId): RecommendationDTO
    {
        $profile = $this->profileRepository->findByUserId($userId);
        
        $userData = [
            'user_id' => $userId,
            'age' => $profile?->age,
            'gender' => $profile?->gender,
            'education' => $profile?->education,
            'occupation' => $profile?->occupation,
            'digital_literacy' => $profile?->digitalLiteracy,
            'device_type' => $profile?->deviceType,
            'visual_preference' => $profile?->visualPreference,
            'context_environment' => $profile?->contextEnvironment,
            'ai_familiarity' => $profile?->aiFamiliarity,
        ];

        try {
            $response = Http::timeout(30)->post(config('app.ml_service_url', 'http://localhost:5000') . '/predict', $userData);
            
            if ($response->successful()) {
                $result = $response->json();
                
                $recommendation = RecommendationDTO::fromArray([
                    'user_id' => $userId,
                    'recommended_ui' => $result['recommended_ui'],
                    'confidence_score' => $result['confidence_score'],
                    'shap_values' => $result['shap_values'] ?? null,
                    'features' => $result['features'] ?? null,
                    'model_version' => $result['model_version'] ?? '1.0',
                ]);
            } else {
                throw new \RuntimeException('ML service error');
            }
        } catch (\Exception $e) {
            $recommendation = RecommendationDTO::fromArray([
                'user_id' => $userId,
                'recommended_ui' => $this->fallbackRecommendation($profile?->visualPreference),
                'confidence_score' => 0.5,
                'features' => [],
                'model_version' => 'fallback',
            ]);
        }
        
        return $this->recommendationRepository->create($recommendation);
    }

    public function getRecommendation(string $userId): ?RecommendationDTO
    {
        $recommendations = $this->recommendationRepository->findByUserId($userId);
        return !empty($recommendations) ? $recommendations[0] : null;
    }

    public function trainModel(): bool
    {
        try {
            $response = Http::timeout(300)->post(config('app.ml_service_url', 'http://localhost:5000') . '/train');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getModelAccuracy(): float
    {
        try {
            $response = Http::timeout(10)->get(config('app.ml_service_url', 'http://localhost:5000') . '/accuracy');
            return $response->successful() ? $response->json()['accuracy'] : 0.0;
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    private function fallbackRecommendation(?string $visualPreference): string
    {
        return match ($visualPreference) {
            'minimal' => 'A',
            'vibrant' => 'B',
            'classic' => 'C',
            'modern' => 'D',
            default => 'A',
        };
    }
}
