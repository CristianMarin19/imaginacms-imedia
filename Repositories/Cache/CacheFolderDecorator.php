<?php

namespace Modules\Media\Repositories\Cache;

use Modules\Media\Entities\File;
use Modules\Media\Repositories\FolderRepository;
use Modules\Core\Icrud\Repositories\Cache\BaseCacheCrudDecorator;
use Modules\Media\Support\Collection\NestedFoldersCollection;

class CacheFolderDecorator extends BaseCacheCrudDecorator implements FolderRepository
{
  public function __construct(FolderRepository $folder)
  {
    parent::__construct();
    $this->entityName = 'media.folder';
    $this->repository = $folder;
    $this->tags = ['media.files'];
  }

  public function findFolder($folderId)
  {
    return $this->repository->findFolder($folderId);
  }

  public function allChildrenOf(File $folder)
  {
    return $this->repository->allChildrenOf($folder);
  }

  public function allNested(): NestedFoldersCollection
  {
    return $this->repository->allNested();
  }

  public function move(File $folder, File $destination): File
  {
    return $this->repository->move($folder, $destination);
  }

  public function findFolderOrRoot($folderId): File
  {
    return $this->repository->findFolderOrRoot($folderId);
  }

  public function destroy($folder)
  {
    $this->cache->tags($this->getTags())->flush();
    return $this->repository->destroy($folder);
  }
}
