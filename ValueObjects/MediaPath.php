<?php

namespace Modules\Media\ValueObjects;

use Illuminate\Support\Facades\Storage;

class MediaPath
{
    /**
     * @var string
     */
    private $path;
  
  /**
   * @var string
   */
  private $disk;
  
  /**
   * @var int
   */
  private $organizationId;

    public function __construct($path, $disk = null, $organizationId = null)
    {
        if (! is_string($path)) {
            throw new \InvalidArgumentException('The path must be a string');
        }
        $this->path = $path;

        $this->disk = $disk;
        
        $this->organizationId = $organizationId;
    }

    /**
     * Get the URL depending on configured disk
     * @param  string  $disk
     * @return string
     */
    public function getUrl($disk = null, $organizationId = null)
    {
        $path = ltrim($this->path, '/');
        $disk = is_null($disk)? is_null($this->disk)? setting('media::filesystem', null, config("asgard.media.config.filesystem")) : $this->disk : $disk;
        return Storage::disk($disk)->url(((!empty($organizationId) || !empty($this->organizationId)) ? 'organization'.($organizationId ?? $this->organizationId).'/' : '' ).$path);
    }

    /**
     * @return string
     */
    public function getRelativeUrl()
    {
        return ((!empty($organizationId) || !empty($this->organizationId)) ? '/organization'.($organizationId ?? $this->organizationId): '' ).$this->path;
    }

    public function __toString()
    {
        try {
            return $this->getUrl($this->disk,$this->organizationId);
        } catch (\Exception $e) {
            return '';
        }
    }
}
