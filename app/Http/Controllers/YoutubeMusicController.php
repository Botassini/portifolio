<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class YoutubeMusicController extends Controller
{
    public function download(Request $request): Response
    {
        set_time_limit(120);
        Log::info('Set time limit to 120 seconds.');

        $youtubeUrl = $request->input('youtube_url');
        Log::info('Received YouTube URL: ' . $youtubeUrl);

        preg_match('/v=([^&]+)/', $youtubeUrl, $matches);
        $videoId = $matches[1] ?? uniqid();
        Log::info('Extracted video ID: ' . $videoId);

        $apiKey = env("YOUTUBE_API_KEY");
        $apiUrl = "https://www.googleapis.com/youtube/v3/videos?id={$videoId}&key={$apiKey}&part=snippet";
        Log::info('Constructed API URL: ' . $apiUrl);

        $response = Http::withOptions(['verify' => false])->get($apiUrl);
        Log::info('API response received.');

        if ($response->failed() || empty($response->json('items'))) {
            Log::info('Failed to get video title.');
            return response()->json(['error' => 'Erro ao obter o título do vídeo.'], 500);
        }

        $videoTitle = $response->json('items')[0]['snippet']['title'];
        Log::info('Video title: ' . $videoTitle);

        $sanitizedTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $videoTitle);
        Log::info('Sanitized video title: ' . $sanitizedTitle);

        $outputDir = getenv('USERPROFILE') . '\Downloads\\';
        Log::info('Output directory: ' . $outputDir);

        $outputTemplate = $outputDir . $sanitizedTitle . '.mp3';
        Log::info('Output template: ' . $outputTemplate);

        $ytDlpPath = storage_path('yt-dlp/yt-dlp.exe');
        Log::info('yt-dlp path: ' . $ytDlpPath);

        $command = escapeshellarg($ytDlpPath) . ' --newline --progress --no-playlist --restrict-filenames -x --audio-format mp3 -o ' . escapeshellarg($outputTemplate) . ' ' . escapeshellarg($youtubeUrl);
        Log::info('Command to be executed: ' . $command);

        $downloadedFilePattern = $outputDir . $sanitizedTitle . '.*';
        Log::info('Downloaded file pattern: ' . $downloadedFilePattern);

        $process = proc_open($command, [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ], $pipes);

        if (is_resource($process)) {
            while (!feof($pipes[1])) {
                $output = fgets($pipes[1]);
                Log::info('Command output: ' . $output);

                $downloadedFiles = glob($downloadedFilePattern);
                if (!empty($downloadedFiles)) {
                    foreach ($downloadedFiles as $file) {
                        if (pathinfo($file, PATHINFO_EXTENSION) !== 'part') {
                            Log::info('File downloaded during command execution.');
                            proc_terminate($process);
                            fclose($pipes[1]);
                            fclose($pipes[2]);
                            proc_close($process);
                            return response()->json(['success' => true, 'message' => 'Download concluído com sucesso!']);
                        }
                    }
                }
            }

            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
        }

        $downloadedFiles = glob($downloadedFilePattern);
        Log::info('Downloaded files: ' . json_encode($downloadedFiles));

        if (empty($downloadedFiles)) {
            Log::info('File not found after download.');
            return response()->json(['error' => 'Arquivo não encontrado após o download.'], 500);
        }

        $downloadedFile = $downloadedFiles[0];
        Log::info('Downloaded file: ' . $downloadedFile);

        return response()->json(['success' => true, 'message' => 'Download concluído com sucesso!']);
    }


    public function preview(Request $request)
    {
        $validatedData = $request->validate(['youtube_url' => 'required|url']);
        $youtubeUrl = $validatedData['youtube_url'];
        preg_match('/v=([a-zA-Z0-9_-]{11})/', $youtubeUrl, $matches);
        if (empty($matches[1])) {
            return response()->json(['error' => __('Invalid YouTube URL.')], 400);
        }
        $videoId = $matches[1];
        $apiKey = env("YOUTUBE_API_KEY");
        $apiUrl = "https://www.googleapis.com/youtube/v3/videos";
        try {
            $response = Http::withoutVerifying()->get($apiUrl, [
                'part' => 'snippet,contentDetails,statistics',
                'id' => $videoId,
                'key' => $apiKey,
            ]);
            if ($response->failed()) {
                return response()->json(['error' => __('Unable to fetch video information.')], 500);
            }
            $videoData = $response->json();
            if (empty($videoData['items'])) {
                return response()->json(['error' => __('No video found.')], 404);
            }
            $videoInfo = $videoData['items'][0];
            $snippet = $videoInfo['snippet'];
            $statistics = $videoInfo['statistics'];
            $contentDetails = $videoInfo['contentDetails'];
            $duration = new \DateInterval($contentDetails['duration']);
            $formattedDuration = $duration->h > 0 ? $duration->format('%H:%I:%S') : $duration->format('%I:%S');
            return response()->json([
                'title' => $snippet['title'],
                'thumbnail' => $snippet['thumbnails']['high']['url'],
                'uploader' => $snippet['channelTitle'],
                'duration' => $formattedDuration,
                'view_count' => $statistics['viewCount'] ?? 'N/A',
                'upload_date' => date("d/m/Y", strtotime($snippet['publishedAt'])),
                'description' => $snippet['description'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => __('Unable to fetch video information.')], 500);
        }
    }
}
