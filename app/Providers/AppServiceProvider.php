<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::macro('inDisplayTimezone', function (): Carbon {
            /** @var Carbon $this */
            return $this->copy()->setTimezone((string) config('app.display_timezone'));
        });

        $uploadLimitBytes = (int) ini_parse_quantity((string) ini_get('upload_max_filesize'));
        $postLimitBytes = (int) ini_parse_quantity((string) ini_get('post_max_size'));
        $configuredLimitKilobytes = (int) config('materials.upload.max_size_kilobytes');
        $uploadLimitKilobytes = $uploadLimitBytes > 0
            ? intdiv($uploadLimitBytes, 1024)
            : 0;
        $postLimitKilobytes = $postLimitBytes > 0
            ? intdiv(max(0, $postLimitBytes - 1024 * 1024), 1024)
            : PHP_INT_MAX;
        $serverLimitKilobytes = min(
            $uploadLimitKilobytes,
            $postLimitKilobytes,
        );
        $effectiveLimitKilobytes = min($configuredLimitKilobytes, $serverLimitKilobytes);
        $configuredProfilePhotoLimitKilobytes = (int) config('profile.photo.max_size_kilobytes');
        $effectiveProfilePhotoLimitKilobytes = min(
            $configuredProfilePhotoLimitKilobytes,
            $serverLimitKilobytes,
        );

        config()->set('materials.upload.max_size_kilobytes', $effectiveLimitKilobytes);
        config()->set('profile.photo.max_size_kilobytes', $effectiveProfilePhotoLimitKilobytes);
        config()->set('livewire.temporary_file_upload.rules', [
            'required',
            'file',
            'mimes:'.implode(',', config('materials.upload.allowed_mimes')),
            'max:'.$effectiveLimitKilobytes,
        ]);
    }
}
