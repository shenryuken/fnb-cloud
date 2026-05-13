<?php

namespace App\Livewire\Landlord;

use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('System Health')]
#[Lazy]
class SystemHealth extends Component
{
    public array $checks = [];

    public function mount(): void
    {
        $this->refreshChecks();
    }

    public function refreshChecks(): void
    {
        $storagePaths = [
            storage_path('framework'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
        ];

        $this->checks = [
            [
                'group' => 'Runtime',
                'label' => 'PHP Version',
                'ok' => true,
                'value' => PHP_VERSION,
            ],
            [
                'group' => 'Runtime',
                'label' => 'ZIP Extension',
                'ok' => extension_loaded('zip') || class_exists(\ZipArchive::class),
                'value' => (extension_loaded('zip') || class_exists(\ZipArchive::class)) ? 'Enabled' : 'Missing',
                'help' => 'Required for ZIP exports (ZipArchive).',
            ],
            [
                'group' => 'Runtime',
                'label' => 'OpenSSL Extension',
                'ok' => extension_loaded('openssl'),
                'value' => extension_loaded('openssl') ? 'Enabled' : 'Missing',
            ],
            [
                'group' => 'Runtime',
                'label' => 'Mbstring Extension',
                'ok' => extension_loaded('mbstring'),
                'value' => extension_loaded('mbstring') ? 'Enabled' : 'Missing',
            ],
            [
                'group' => 'Config',
                'label' => 'Environment',
                'ok' => true,
                'value' => (string) config('app.env'),
            ],
            [
                'group' => 'Config',
                'label' => 'Debug Mode',
                'ok' => !(bool) config('app.debug'),
                'value' => (bool) config('app.debug') ? 'Enabled' : 'Disabled',
                'help' => 'Should be disabled in production.',
            ],
            [
                'group' => 'Config',
                'label' => 'Cache Driver',
                'ok' => true,
                'value' => (string) config('cache.default'),
            ],
            [
                'group' => 'Config',
                'label' => 'Queue Driver',
                'ok' => true,
                'value' => (string) config('queue.default'),
            ],
            [
                'group' => 'Storage',
                'label' => 'Storage Writable',
                'ok' => collect($storagePaths)->every(fn ($p) => is_dir($p) && is_writable($p)),
                'value' => collect($storagePaths)
                    ->map(fn ($p) => basename($p) . ':' . ((is_dir($p) && is_writable($p)) ? 'ok' : 'bad'))
                    ->implode('  '),
                'help' => 'Ensure storage/framework and storage/logs are writable by the web server.',
            ],
        ];
    }

    public function render()
    {
        $groups = collect($this->checks)->groupBy('group')->all();

        return view('livewire.landlord.system-health', [
            'groups' => $groups,
        ]);
    }
}

