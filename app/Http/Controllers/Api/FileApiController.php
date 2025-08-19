<?php

namespace Modules\Imedia\Http\Controllers\Api;

use Imagina\Icore\Http\Controllers\CoreApiController;
//Model
use Modules\Imedia\Models\File;
use Modules\Imedia\Repositories\FileRepository;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Imagina\Icore\Transformers\CoreResource;

//Services
use Modules\Imedia\Services\FileStoreService;
use Modules\Imedia\Services\FolderService;

class FileApiController extends CoreApiController
{

    private $fileStoreService;

    private $folderService;

    public function __construct(
        File $model,
        FileRepository $modelRepository,
        FileStoreService $fileStoreService,
        FolderService $folderService
    ) {
        parent::__construct($model, $modelRepository);
        $this->fileStoreService = $fileStoreService;
        $this->folderService = $folderService;
    }

    /**
     * Controller to create model
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {

        DB::beginTransaction();
        try {

            //Get model data
            $modelData = $request->input('attributes') ?? [];

            if (isset($modelData['name'])) {
                //Process Folder
                $savedModel = $this->folderService->store($modelData);
            } else {
                //Proccess File
                if ($request->hasFile('file')) {
                    $file = $request->file('file');
                    $savedModel = $this->fileStoreService->storeFromMultipart($file, [], $modelData);
                } else {
                    throw new \Exception(Response::$statusTexts[Response::HTTP_NOT_FOUND], Response::HTTP_NOT_FOUND);
                }
            }


            //Response
            $response = ['data' => CoreResource::transformData($savedModel)];
            DB::commit(); //Commit to Data Base
        } catch (\Exception $e) {
            DB::rollback(); //Rollback to Data Base
            [$status, $response] = $this->getErrorResponse($e);
        }
        //Return response
        return response()->json($response, $status ?? Response::HTTP_CREATED);
    }
}
