<?php

namespace Modules\Media\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Icrud\Repositories\BaseCrudRepository;
use Modules\Media\Entities\File;
use Modules\Media\Support\Collection\NestedFoldersCollection;

interface FolderRepository extends BaseCrudRepository
{
  /**
   * Find a folder by its ID
   *
   * @param  int  $folderId
   * @return File|null
   */
  public function findFolder($folderId);

  /**
   * @return Collection
   */
  public function allChildrenOf(File $folder);

  public function allNested(): NestedFoldersCollection;

  public function move(File $folder, File $destination): File;

  /**
   * Find the folder by ID or return a root folder
   * which is an instantiated File class
   *
   * @param  int  $folderId
   */
  public function findFolderOrRoot($folderId): File;
}
