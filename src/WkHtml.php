<?php

namespace Frengky\WkHtml;

use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Psr7;

abstract class WkHtml
{
    /** @var StreamInterface */
    protected $source;

    /** @var array */
    protected $errors = [];

    /** @var array */
    protected $args = [];

    /**
     * Create new instance
     * 
     * @param resource|string|StreamInterface|callable $source
     */
    public static function fromHtml($source) {
        $instance = new static;
        $instance->source = Psr7\stream_for($source);

        return $instance;
    }

    /**
     * Set executable to use
     * 
     * @param string $binary
     */
    public static function setExecutable($binary) {
        static::$executable = $binary;
    }

    /**
     * Set the command args
     * 
     * @param string $arg
     * @param optional string $value
     * 
     * @return PDF
     */
    public function set($arg, $value = null) {
        $this->args[] = is_null($value) ? $arg : $arg . ' ' . escapeshellarg($value);
        return $this;
    }

    /**
     * Render the html into a pdf/image file
     * 
     * @param string $filename
     * @param int $dirmode
     * @return string|bool
     */
    public function saveAs($filename, $dirmode = 0777) {
        $directory = dirname($filename);
        if (! file_exists($directory)) {
            if (! mkdir($directory, $dirmode, true)) {
                throw new \RuntimeException('unable to create directory ' . $directory);
            }
        } else if (! is_writeable($directory)) {
            throw new \RuntimeException('permission denied on ' . $directory);
        }

        if ($this->render(function($output) use($filename) {
            Psr7\copy_to_stream($output, Psr7\stream_for(fopen($filename, 'w')));   
        })) {
            return $filename;
        }

        return false;
    }

    /**
     * Render the html and return the result
     * 
     * @return null|mixed
     */
    public function make() {
        $result = null;
        $this->render(function($output) use(&$result) {
            $result = $output->getContents();
        });

        return $result;
    }

    /**
     * Render the html into a stream or callable with stream
     * 
     * @param StreamInterface|callable $target
     * @return bool
     */
    public function render($target) {
        $process = proc_open($this->getExecutableCommand(), [
            0 => [ 'pipe', 'r' ], // stdin
            1 => [ 'pipe', 'w' ], // stdout
            2 => [ 'pipe', stripos(PHP_OS, 'WIN') !== false ? 'a' : 'w' ], // stderr
        ], $pipes);
        
        if (! is_resource($process)) {
            return false;
        }

        $inputStream = Psr7\stream_for($pipes[0]);
        Psr7\copy_to_stream($this->source, $inputStream);
        $inputStream->close();

        if (stripos(PHP_OS, 'WIN') !== false) {
            $outputStream = Psr7\stream_for(stream_get_contents($pipes[1]));
            fclose($pipes[1]);
        } else {
            stream_set_blocking($pipes[1], false);
            $outputStream = new ReadOnlyStream(Psr7\stream_for($pipes[1]));
        }

        do {
            $status = proc_get_status($process);
            if (! feof($pipes[2])) {
                $this->errors[] = fgets($pipes[2]);
            }
        } while($status['running']);

        $exitCode = $status['exitcode'];
        if ($exitCode === 0) {
            if (is_callable($target)) {
                call_user_func($target, $outputStream);
            } else if ($target instanceof StreamInterface) {
                if ($target->isWritable()) {
                    Psr7\copy_to_stream($outputStream, $target);
                }
            }
        }

        $outputStream->close();
        proc_close($process);

        return $exitCode === 0;
    }

    /** 
     * Get errors captured from stderr
     * 
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Construct the command line
     * 
     * @return string
     */
    protected function getExecutableCommand() {
        $command = static::$executable;
        if (! empty($this->args)) {
            $command .= ' ' . join(' ', $this->args);
        }
        return $command . ' - -';
    }
}