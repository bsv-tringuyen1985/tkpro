<?php
// Merge the old CMS volume without deleting or overwriting either copy.
$source = $argv[1] ?? '/legacy-media';
$destination = $argv[2] ?? '/var/www/html/storage/app/public';
if (! is_dir($source)) { exit(0); }
$marker = $destination.'/.legacy-cms-media-imported';
if (is_file($marker)) { exit(0); }
$source = rtrim(realpath($source), DIRECTORY_SEPARATOR);
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS));
foreach ($files as $file) {
    if ($file->isLink()) { throw new RuntimeException('Symlink found in legacy media; manual review required.'); }
    if (! $file->isFile()) { continue; }
    $target = $destination.DIRECTORY_SEPARATOR.substr($file->getPathname(), strlen($source) + 1);
    if (is_file($target)) {
        if (hash_file('sha256', $target) !== hash_file('sha256', $file->getPathname())) {
            throw new RuntimeException('Media collision; neither file was overwritten: '.$target);
        }
        continue;
    }
    if (! is_dir(dirname($target)) && ! mkdir(dirname($target), 0775, true) && ! is_dir(dirname($target))) {
        throw new RuntimeException('Cannot create media directory.');
    }
    if (! copy($file->getPathname(), $target)) { throw new RuntimeException('Cannot copy legacy media.'); }
}
if (! is_dir(dirname($marker))) { mkdir(dirname($marker), 0775, true); }
if (file_put_contents($marker, "Imported successfully\n") === false) {
    throw new RuntimeException('Cannot record successful media import.');
}
