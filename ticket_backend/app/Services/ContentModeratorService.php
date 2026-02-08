<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContentModeratorService
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        // SỬ DỤNG: gemini-flash-latest (Bản ổn định, Free Tier OK)
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=' . $this->apiKey;
    }

    public function checkContent($text)
    {
        try {
            // Prompt bắt buộc trả về JSON
            $prompt = "
                Bạn là hệ thống kiểm duyệt bình luận sự kiện. Phân tích: '{$text}'.
                Tiêu chí: Chửi thề, xúc phạm, lừa đảo, thù địch.
                Trả về JSON duy nhất:
                { \"is_safe\": true/false, \"reason\": \"Lý do ngắn gọn (tiếng Việt) nếu false\", \"score\": 0-10 }
            ";

            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($this->apiUrl, [
                    'contents' => [['parts' => [['text' => $prompt]]]]
                ]);

            if ($response->failed()) {
                Log::error('Gemini API Error: ' . $response->body());
                return ['is_safe' => true]; // API lỗi thì cho qua để không chặn user
            }

            $data = $response->json();
            $rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            // Làm sạch chuỗi JSON (xóa markdown ```json nếu có)
            $cleanJson = str_replace(['```json', '```'], '', $rawText);
            
            return json_decode($cleanJson, true) ?? ['is_safe' => true];

        } catch (\Exception $e) {
            Log::error('Moderation Exception: ' . $e->getMessage());
            return ['is_safe' => true];
        }
    }
}