<?php

namespace App\Http\Controllers;

use Alchemy\BinaryDriver\Exception\ExecutionFailureException;
use App\Jobs\TreatMediaProcess;
use Exception;
use FFMpeg\Format\Video\X264;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\FilesystemException;
use Pion\Laravel\ChunkUpload\Exceptions\UploadFailedException;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    /**
     * @throws UploadFailedException
     */
    public function createTempMedia(Request $request)
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

//            $treatment = $this->launchHlsTreatment($fileName);
            Session::put('temporary.filename', $fileName);

            return [
                'path' => env('BO_URL').'/data/medias/'.$fileName,
                'fileName'=>$fileName
            ];
        }
        $handler = $fileReceived->handler();
        return [
            'done' => $handler->getPercentageDone(),
            'status' => true
        ];
    }

    public function getHlsTreatment($filename):JsonResponse{

        TreatMediaProcess::dispatchAfterResponse($filename);
        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    public function launchHlsTreatment($filename)
    {
        $apiUrl = env('API_MANAGER_URL').'/api/media/changeStatus/'.$filename.'/'.explode('.',$filename)[0].'/'.env('MEDIA_INTREATMENT');
        $response = Http::get($apiUrl);
        logger($response);

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

//            try {
//                Storage::disk('sftp')->delete($filename);
//            }catch (\ErrorException $exception){
//                logger((string) $exception);
//            }

            $fileUrl = env('BO_URL').'/data/medias/'.$newFileName;
            $apiUrl = env('API_MANAGER_URL').'/api/media/changeStatus/'.$newFileName.'/'.explode('.',$filename)[0].'/'.env('MEDIA_TREATMENT_SUCCESS');
            logger($apiUrl);
            $response  = Http::get($apiUrl);
            logger($response);

            return [
                'path' => $fileUrl,
                'filename' => $newFileName
            ];

        }catch (\ErrorException|ExecutionFailureException|\RuntimeException|Exception|FilesystemException $exception){
            logger($exception->getMessage());
            $apiUrl = env('API_MANAGER_URL').'/api/media/changeStatus/null/'.explode('.',$filename)[0].'/'.env('MEDIA_TREATMENT_FAILED');
            logger($apiUrl);
            $response = Http::get($apiUrl);
            logger($response);
            return $exception;
        }
    }

}
