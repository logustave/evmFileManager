<?php

namespace App\Http\Controllers;

use Exception;
use FFMpeg\Format\Video\X264;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Pion\Laravel\ChunkUpload\Exceptions\UploadFailedException;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class MediaController extends Controller
{
    /**
     * @throws UploadFailedException
     */
    public function createTempMedia(Request $request): Exception|\ErrorException|array
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));
        if (!$receiver->isUploaded()) {
            // file not uploaded
        }
        $fileReceived = $receiver->receive();
        if ($fileReceived->isFinished()) {
            $file = $fileReceived->getFile();
            $extension = $file->getClientOriginalExtension();
            $fileName = Str::uuid().'.'.$extension;
            Storage::disk('sftp')->put($fileName, $file->getContent());


            unlink($file->getPathname());

            $treatment = $this->launchHlsTreatment($fileName);
            Session::put('temporary.filename', $treatment['filename']);

            return $treatment;
        }
        $handler = $fileReceived->handler();
        return [
            'done' => $handler->getPercentageDone(),
            'status' => true
        ];
    }

    public function launchHlsTreatment($filename): Exception|\ErrorException|array
    {
        $highLowBitrate = (new X264)->setKiloBitrate(150);
        $midBitrate = (new X264)->setKiloBitrate(500);
        $highBitrate = (new X264)->setKiloBitrate(1000);
        $newFileName = explode('.',$filename)[0].'.m3u8';
        try {
            $treatment = FFMpeg::FromDisk('sftp')
                ->open($filename)
                ->exportForHLS()
                ->addFormat($highLowBitrate, function ($media){
                    $media->addFilter('scale=640:360');
                })
                ->addFormat($midBitrate, function($media) {
                    $media->scale(960, 720);
                })
                ->addFormat($highBitrate, function ($media) {
                    $media->addFilter(function ($filters, $in, $out) {
                        $filters->custom($in, 'scale=1920:1080', $out);
                    });
                })
                ->inFormat(new X264('libmp3lame', 'libx264'))
                ->save($newFileName);

            $treatment->cleanupTemporaryFiles();

            try {
                Storage::disk('medias')->delete($filename);
            }catch (\ErrorException $exception){
                logger((string) $exception);
            }

            return [
                'path' => asset('data/medias/'.$newFileName),
                'filename' => $newFileName
            ];

        }catch (\ErrorException $exception){
            logger((string) $exception);
            return $exception;
        }
    }

}
