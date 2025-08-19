<?php

namespace Modules\Imedia\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Imedia\Events\FileIsDeleting;
use Modules\Imedia\Events\Handlers\DeleteFilesInDisk;

use Modules\Imedia\Events\CreateMedia;
use Modules\Imedia\Events\UpdateMedia;
use Modules\Imedia\Events\Handlers\StoreImageable;
use Modules\Imedia\Events\DeleteMedia;
use Modules\Imedia\Events\Handlers\DeleteImageable;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        FileIsDeleting::class => [
            DeleteFilesInDisk::class,
        ],
        CreateMedia::class => [
            StoreImageable::class,
        ],
        UpdateMedia::class => [
            StoreImageable::class,
        ],
        DeleteMedia::class => [
            DeleteImageable::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
