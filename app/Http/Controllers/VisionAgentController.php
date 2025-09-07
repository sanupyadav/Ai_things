<?php

namespace App\Http\Controllers;

use setasign\Fpdi\Fpdi;
use Illuminate\Http\Request;
use App\AiAgents\visionAgent;
use Aws\Textract\TextractClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\StorageOCR;
use thiagoalessio\TesseractOCR\TesseractOCR;

class VisionAgentController extends Controller
{
    public function index()
    {
        return view('vision'); // Blade view with form
    }

    public function analyze(Request $request)
    {
        set_time_limit(120);
        $request->validate([
            'images.*' => 'required|image|max:5120',
        ]);

        // Step 1: OCR
        // $text = $this->OCR($request->file('images'));
        // dd($text);
        // Step 2: Convert images to base64
        $base64Images = [];
        foreach ($request->file('images') as $imageFile) {
            $imagePath = $imageFile->getRealPath();
            $base64Images[] = "data:" . $imageFile->getMimeType() . ";base64," . base64_encode(file_get_contents($imagePath));
        }
        $allBase64 = implode("\n\n", $base64Images);

        // Step 3: AI structured mapping
        $response = visionAgent::for('vision_analysis')
                    ->withImages($base64Images)
                    ->respond(
                        "Analyze the following credit card images (base64) and extract structured JSON for each:\n\n" 
                        . $allBase64,
                        ['temperature' => 0] // deterministic output
                    );

        return response()->json([
            'status' => 'success',
            'data'   => $response,
            //'extracted_text' => $text,
            // 'structured_json' => $response
        ]);
    }


    // public function analyze(Request $request)
    // {
    //     $request->validate([
    //         'images.*' => 'required|image|max:5120', // 5MB per image
    //     ]);

    //     $images = $request->file('images');
    //     $responses = [];

    //     // Step 0: Ensure temp folder exists
    //     $tempFolder = storage_path('app/temp');
    //     if (!file_exists($tempFolder)) {
    //         mkdir($tempFolder, 0755, true);
    //     }

    //     // Step 1: Convert multiple images to single PDF
    //     $pdf = new Fpdi();

    //     foreach ($images as $imageFile) {
    //         $extension = strtolower($imageFile->getClientOriginalExtension()); // jpg, png, etc.
    //         $tmpPath = sys_get_temp_dir() . '/' . uniqid() . '.' . $extension;
    //         copy($imageFile->getRealPath(), $tmpPath);

    //         $pdf->AddPage();
    //         $pdf->Image($tmpPath, 0, 0, 210, 297); // A4 size
    //     }

    //     $pdfPath = $tempFolder . '/cards.pdf';
    //     $pdf->Output($pdfPath, 'F');

    //     // Step 2: Upload PDF to S3
    //     $s3Key = 'temp/cards.pdf';
    //     Storage::disk('s3')->put($s3Key, file_get_contents($pdfPath));

    //     // Step 3: Initialize AWS Textract client
    //     $client = new TextractClient([
    //         'version' => 'latest',
    //         'region'  => env('AWS_DEFAULT_REGION'),
    //         'credentials' => [
    //             'key' => env('AWS_ACCESS_KEY_ID'),
    //             'secret' => env('AWS_SECRET_ACCESS_KEY'),
    //         ],
    //     ]);

    //     // Step 4: Start async Textract job
    //     $result = $client->startDocumentAnalysis([
    //         'DocumentLocation' => [
    //             'S3Object' => [
    //                 'Bucket' => env('AWS_BUCKET'),
    //                 'Name' => $s3Key,
    //             ],
    //         ],
    //         'FeatureTypes' => ['FORMS'],
    //     ]);

    //     $jobId = $result['JobId'];

    //     // Step 5: Poll until job completes
    //     $completed = false;
    //     while (!$completed) {
    //         sleep(2);
    //         $status = $client->getDocumentAnalysis(['JobId' => $jobId]);
    //         if (in_array($status['JobStatus'], ['SUCCEEDED', 'FAILED'])) {
    //             $completed = true;
    //         }
    //     }

    //     // Step 6: Process Textract output
    //     if ($status['JobStatus'] == 'SUCCEEDED') {
    //         $blocks = $status['Blocks'];
    //         $parsedData = $this->parseTextractBlocks($blocks);

    //         // Cleanup temp PDF
    //         if (file_exists($pdfPath)) {
    //             unlink($pdfPath);
    //         }

    //         return response()->json($parsedData);
    //     } else {
    //         return response()->json(['error' => 'Textract analysis failed']);
    //     }
    // }

    // private function parseTextractBlocks($blocks)
    // {
    //     $results = [];

    //     // Example: naive parsing, improve by regex/key detection
    //     foreach($blocks as $block){
    //         if($block['BlockType'] === 'LINE'){
    //             $text = $block['Text'];
    //             // Example: simple regex for card number
    //             if(preg_match('/\b\d{4}\s\d{4}\s\d{4}\s\d{4}\b/', $text, $matches)){
    //                 $results['card_number'] = $matches[0];
    //             }
    //             // Example: expiry MM/YY
    //             if(preg_match('/\b(0[1-9]|1[0-2])\/\d{2}\b/', $text, $matches)){
    //                 $results['expiry_date'] = $matches[0];
    //             }
    //             // Example: card holder name (all uppercase words)
    //             if(preg_match('/^[A-Z\s]{3,}$/', $text)){
    //                 $results['card_holder'] = $text;
    //             }
    //             // TODO: CVV & card type detection
    //         }
    //     }

    //     return $results;
    // }

}
