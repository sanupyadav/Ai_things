<?php

namespace App\Services;

use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Mp3;
use FFMpeg\Format\Audio\Wav;

class AudioService
{
    protected FFMpeg $ffmpeg;
    protected string $outputFolder;

    public function __construct()
    {
        $this->ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => '/opt/homebrew/bin/ffmpeg',
            'ffprobe.binaries' => '/opt/homebrew/bin/ffprobe',
            'timeout'          => 3600,
            'ffmpeg.threads'   => 12,
        ]);

        // Define folder to store all processed audio
        $this->outputFolder = storage_path('app/public/processed_audio');

        // Create folder if it doesn't exist
        if (!file_exists($this->outputFolder)) {
            mkdir($this->outputFolder, 0755, true);
        }
    }

    /**
     * Preprocess audio:
     * - Resample to 16kHz mono
     * - Remove background noise
     * - Convert to MP3 or WAV
     * - Store in a single folder
     */
    public function preprocess(string $filePath, string $format = 'mp3'): string
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: " . $filePath);
        }

        $audio = $this->ffmpeg->open($filePath);

        // Apply noise reduction
        $audio->filters()->custom('afftdn=nf=-25');

        // Choose output format
       if ($format === 'mp3') {
            $audioFormat = new Mp3();
            $audioFormat->setAudioCodec('libmp3lame');
        } elseif ($format === 'flac') {
            $audioFormat = new Flac();
            $audioFormat->setAudioCodec('flac');
        } else {
            $audioFormat = new Wav();
            $audioFormat->setAudioCodec('pcm_s16le');
        }

        $audioFormat
            ->setAudioKiloBitrate(64)
            ->setAudioChannels(1);

        // Generate output filename (original name + timestamp)
        $fileName = pathinfo($filePath, PATHINFO_FILENAME);
        $timestamp = time();
        $outputPath = $this->outputFolder . '/' . $fileName . '_' . $timestamp . '.' . $format;

        // Save processed audio
        $audio->save($audioFormat, $outputPath);

        return $outputPath;
    }
}
