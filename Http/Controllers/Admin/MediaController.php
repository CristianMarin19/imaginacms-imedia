<?php

namespace Modules\Media\Http\Controllers\Admin;

use Illuminate\Contracts\Config\Repository;
use Modules\Core\Http\Controllers\Admin\AdminBaseController;
use Modules\Media\Entities\File;
use Modules\Media\Http\Requests\UpdateMediaRequest;
use Modules\Media\Image\Imagy;
use Modules\Media\Image\ThumbnailManager;
use Modules\Media\Repositories\FileRepository;

class MediaController extends AdminBaseController
{
    /**
     * @var FileRepository
     */
    private $file;

    /**
     * @var Repository
     */
    private $config;

    /**
     * @var Imagy
     */
    private $imagy;

    /**
     * @var ThumbnailManager
     */
    private $thumbnailsManager;

    public function __construct(FileRepository $file, Repository $config, Imagy $imagy, ThumbnailManager $thumbnailsManager)
    {
        parent::__construct();
        $this->file = $file;
        $this->config = $config;
        $this->imagy = $imagy;
        $this->thumbnailsManager = $thumbnailsManager;
    }

    public function index(): \Illuminate\View\View
    {
        $config = $this->config->get('asgard.media.config');

        return view('media::admin.index', compact('config'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return view('media.create');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(File $file): Response
    {
        $thumbnails = $this->thumbnailsManager->all();

        return view('media::admin.edit', compact('file', 'thumbnails'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(File $file, UpdateMediaRequest $request): Response
    {
        $this->file->update($file, $request->all());

        return redirect()->route('admin.media.media.index')
            ->withSuccess(trans('media::messages.file updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @internal param int $id
     */
    public function destroy(File $file): Response
    {
        $this->imagy->deleteAllFor($file);
        $this->file->destroy($file);

        return redirect()->route('admin.media.media.index')
            ->withSuccess(trans('media::messages.file deleted'));
    }
}
