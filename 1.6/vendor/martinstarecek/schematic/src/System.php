<?php

namespace RoundingWell\Schematic;

/**
 * @codeCoverageIgnore
 */
class System
{
    public function writeFile($file, $contents)
    {
        $directory = pathinfo($file, PATHINFO_DIRNAME);

        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        return (int) file_put_contents($file, $contents);
    }
}
