<?php

namespace Modules\Media\View\Components;

use Illuminate\Support\Str;
use Illuminate\View\Component;

class Gallery extends Component
{
  public $id;
  public $zones;
  public $mediaFiles;
  public $margin;
  public $responsiveClass;
  public $autoplay;
  public $autoplayHoverPause;
  public $loopGallery;
  public $dots;
  public $nav;
  public $responsive;
  public $gallery;
  public $dataFancybox;
  public $view;
  public $columnMasonry;
  public $navText;
  public $showNavs;
  public $maxImages;
  public $autoplayVideo;
  public $mutedVideo;
  public $loopVideo;
  public $stagePadding;
  public $autoplayTimeout;
  public $aspectRatio;
  public $objectFit;
  public $showDescription;
  public $marginItems;
  public $heightItems;
  public $componentId;

  /**
   * Create a new component instance.
   *
   * @return void
   */

  public function __construct($mediaFiles, $id = "gallery", $zones = ["gallery"], $margin = 10, $responsiveClass = true,
                              $autoplay = true, $autoplayHoverPause = true, $loopGallery = true, $dots = true, $nav = true,
                              $showNavs = true, $responsive = null, $dataFancybox = 'gallery', $layout = "gallery-layout-1",
                              $columnMasonry = 3, $navText = "", $maxImages = null, $onlyVideos = false,
                              $onlyImages = false, $autoplayVideo = false, $mutedVideo = false, $loopVideo = false,
                              $stagePadding = 0, $autoplayTimeout = 5000, $aspectRatio = "1-1", $objectFit = 'contain',
                              $showDescription = false, $marginItems = 0, $heightItems = 350, $componentId = null
  )
  {
    $this->componentId = $componentId ?? "";
    $this->id = $id;
    $this->view = "media::frontend.components.gallery.layouts.$layout.index";
    $this->zones = $zones;
    $this->mediaFiles = $mediaFiles;
    $this->margin = $margin;
    $this->responsiveClass = $responsiveClass;
    $this->autoplay = $autoplay;
    $this->autoplayHoverPause = $autoplayHoverPause;
    $this->loopGallery = $loopGallery;
    $this->dots = $dots;
    $this->nav = $nav;
    $this->showNavs = $showNavs;
    $this->responsive = json_encode($responsive ?? [0 => ["items" => 1], 640 => ["items" => 2], 992 => ["items" => 4]]);
    if (gettype($responsive) === 'string') $this->responsive = $responsive;
    $this->dataFancybox = $dataFancybox ? $dataFancybox . Str::uuid() : null;
    $this->gallery = [];
    $this->columnMasonry = $columnMasonry;
    $this->navText = json_encode($navText);
    $this->maxImages = $maxImages;
    $this->autoplayVideo = $autoplayVideo;
    $this->mutedVideo = $mutedVideo;
    $this->loopVideo = $loopVideo;
    $this->stagePadding = $stagePadding;
    $this->autoplayTimeout = $autoplayTimeout;
    $this->aspectRatio = $aspectRatio;
    $this->objectFit = $objectFit;
    $this->showDescription = $showDescription;
    $this->marginItems = $marginItems;
    $this->heightItems = $heightItems;

    if (!empty($mediaFiles)) {
      $countImages = 0;
      foreach ($zones as $zone) {
        !is_array($mediaFiles->{$zone}) ? $mediaFiles->{$zone} = [$mediaFiles->{$zone}] : false;
        foreach ($mediaFiles->{$zone} as $itemImage) {
          if (empty($maxImages) || $countImages < $maxImages) {
            if (($onlyImages && $itemImage->isImage) || ($onlyVideos && $itemImage->isVideo) || (!$onlyVideos && !$onlyImages)) {
              $countImages++;
              array_push($this->gallery, $itemImage);
            }

          }
        }

      }
    }
  }

  /**
   * Get the view / contents that represent the component.
   *
   * @return \Illuminate\View\View|string
   */
  public function render()
  {
    return view($this->view);
  }
}
