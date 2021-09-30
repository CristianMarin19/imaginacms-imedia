<?php

namespace Modules\Media\View\Components;

use Illuminate\View\Component;

class SingleImage extends Component
{
  /**
   * Create a new component instance.
   *
   * @return void
   */
  public $src;
  public $alt;
  public $title;
  public $fallbackExtension;
  public $url;
  public $extraLargeSrc;
  public $fallback;
  public $largeSrc;
  public $mediumSrc;
  public $smallSrc;
  public $imgClasses;
  public $linkClasses;
  public $defaultLinkClasses;
  public $imgStyles;
  public $width;
  public $dataFancybox;
  public $dataCaption;
  
  public function __construct($src = '', $alt = '', $title = null, $url = null, $isMedia = false, $mediaFiles = null,
                              $zone = 'mainimage', $extraLargeSrc = null, $largeSrc = null, $mediumSrc = null,
                              $smallSrc = null, $fallback = null, $imgClasses = '', $linkClasses = '', $defaultLinkClasses = 'image-link w-100',
                              $imgStyles = '', $width = "300px", $dataFancybox = null, $dataCaption = null)
  {
    $this->src = $src;
    $this->alt = $alt;
    $this->title = $title;
    $this->url = $url;
    $this->imgClasses = $imgClasses;
    $this->linkClasses = $linkClasses;
    $this->defaultLinkClasses = $defaultLinkClasses;
    $this->imgStyles = $imgStyles;
    $this->width = $width;
    $this->dataFancybox = $dataFancybox;
    $this->dataCaption = $dataCaption;
    
    if (!empty($fallback)) {
      $this->fallbackExtension = pathinfo($fallback, PATHINFO_EXTENSION);
      if ($this->fallbackExtension == "jpg") $this->fallbackExtension = "jpeg";
    }
    
   
    if($isMedia && !empty($mediaFiles)){
      $this->src = $mediaFiles->{$zone}->extraLargeThumb ?? $mediaFiles->extraLargeThumb;
      $this->fallback = $mediaFiles->{$zone}->path ?? $mediaFiles->path;
      $this->extraLargeSrc = $mediaFiles->{$zone}->extraLargeThumb ?? $mediaFiles->extraLargeThumb;
      $this->largeSrc = $mediaFiles->{$zone}->largeThumb ?? $mediaFiles->largeThumb;
      $this->mediumSrc = $mediaFiles->{$zone}->mediumThumb ?? $mediaFiles->mediumThumb;
      $this->smallSrc = $mediaFiles->{$zone}->smallThumb ?? $mediaFiles->smallThumb;
      
    }else{
      $this->extraLargeSrc = $extraLargeSrc;
      $this->largeSrc = $largeSrc;
      $this->mediumSrc = $mediumSrc;
      $this->smallSrc = $smallSrc;
      $this->fallback = $fallback ?? $src;
    }
 
  }
  
  /**
   * Get the view / contents that represent the component.
   *
   * @return \Illuminate\View\View|string
   */
  public function render()
  {
    return view('media::frontend.components.singleimage');
  }
}