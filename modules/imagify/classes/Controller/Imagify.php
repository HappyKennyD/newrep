<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Imagify extends Controller {

    public function action_process()
    {
        $options    = (string)$this->request->param('options', '');
        $path       = (string)$this->request->param('image', '');

        $config     = Kohana::$config->load('imagify');
        $savedir    = realpath($config->get('save_directory')) . DIRECTORY_SEPARATOR;
        $loaddir    = realpath($config->get('image_directory')) . DIRECTORY_SEPARATOR;
        $caching    = $config->get('cache', false);

        $path = str_replace('\\', '/', $path);
        $p = explode('/', $path);
        $name = array_pop($p);

        if (!is_file($loaddir . $path))
        {
            throw new HTTP_Exception_404;
        }

        $image = Imagify::instance();
        try
        {
            $image->process($options, $loaddir . $path);

        }
        catch (Imagify_Exception $e)
        {
            throw new HTTP_Exception_404($e->getMessage());
        }

        if ($caching)
        {
            $directory = $savedir;
            array_unshift($p, $options);
            for ($i=0; $i < count($p); $i++) // creating sub-directories
            {
                $directory .= $p[$i] . DIRECTORY_SEPARATOR;
                if (!is_dir($directory))
                {
                    mkdir($directory, 02777);
                    chmod($directory, 02777);
                }
            }

            $directory = realpath($directory);
            $savedir = realpath($savedir);

            $savedirectory = substr($savedir, 0, strlen($savedir));

            if ($savedir !== $savedirectory) // check if we run out of the our cozy directory
            {
                throw new HTTP_Exception_404;
            }

            $image->save($directory . DIRECTORY_SEPARATOR .  $name);
        }
        echo $image->headers()->render();
    }
}