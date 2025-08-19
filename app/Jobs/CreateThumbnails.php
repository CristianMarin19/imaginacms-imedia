<?php

namespace Modules\Imedia\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Imedia\Models\File;
use Modules\Imedia\Services\ThumbnailService;

class CreateThumbnails implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable, Dispatchable;


    /**
     * @var mixed|null
     */
    private $file;
    private $thumbnailService;


    public function __construct(File $file)
    {
        $this->file = $file;
        $this->queue = "media";
        $this->thumbnailService = new ThumbnailService();
    }

    public function handle()
    {

        $this->thumbnailService->generateThumbnails($this->file);

        //update attribute has_thumbnails
        $this->file->has_thumbnails = true;
        $this->file->update();
    }
}
