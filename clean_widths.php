<?php

$dir = __DIR__ . '/app/Filament/Resources';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        
        if (strpos($content, 'extends CreateRecord') !== false || strpos($content, 'extends EditRecord') !== false) {
            
            // Clean up all getMaxContentWidth insertions
            $pattern = '/\s*public function getMaxContentWidth\(\)(?::\s*.*?)?\s*\{\s*return \'6xl\';\s*\}/s';
            $cleaned = preg_replace($pattern, '', $content);
            
            if ($cleaned !== $content) {
                file_put_contents($file->getPathname(), $cleaned);
                echo "Cleaned: " . $file->getFilename() . "\n";
            }
        }
    }
}
echo "Done.\n";
