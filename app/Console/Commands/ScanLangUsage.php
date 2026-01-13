<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

class ScanLangUsage extends Command
{
    protected $signature = 'scan:views-lang';
    protected $description = 'Scan Blade views for risky lang usage (guest-unsafe)';

    public function handle(): int
    {
        $finder = (new Finder())
            ->files()
            ->in(resource_path('views'))
            ->name('*.blade.php');

        $patterns = [
            'Auth::user()->lang' => '/Auth::user\(\)\s*->\s*lang/',
            '->lang (any object)' => '/->\s*lang\b/',
            'guest-unsafe if (...)' => '/@if\s*\(\s*Auth::user\(\)\s*->\s*lang/',
        ];

        $this->info('Scanning Blade views for lang access...');
        foreach ($finder as $file) {
            $path = $file->getRealPath();
            $lines = explode("\n", $file->getContents());
            foreach ($lines as $num => $line) {
                foreach ($patterns as $label => $regex) {
                    if (preg_match($regex, $line)) {
                        $this->line(sprintf(
                            "%s:%d [%s] %s",
                            $path,
                            $num + 1,
                            $label,
                            trim($line)
                        ));
                    }
                }
            }
        }

        $this->info('Scan complete.');
        return Command::SUCCESS;
    }
}

