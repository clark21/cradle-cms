<?php //-->

use Cradle\Module\Utility\File;

return function ($request, $response) {
    //get handlebars
    $handlebars = $this->package('global')->handlebars();

    //add cache folder
    //$handlebars->setCache(__DIR__.'/../compiled');

    $handlebars->registerHelper('sort', function ($name, $options) {
        $value = null;
        if (isset($_GET['order'][$name])) {
            $value = $_GET['order'][$name];
        }

        return $options['fn'](['value' => $value]);
    });

    $handlebars->registerHelper('inspect', function ($mixed) {
        return var_export($mixed, true);
    });

    $handlebars->registerHelper('char_length', function ($value, $length) {
        return strlen($value, $length);
    });

    $handlebars->registerHelper('word_length', function ($value, $length) {
        if (str_word_count($value, 0) > $length) {
            $words = str_word_count($value, 2);
            $position = array_keys($words);
            $value = substr($value, 0, $position[$length]);
        }

        return $value;
    });

    $handlebars->registerHelper('toucfirst', function ($value) {
        return ucfirst($value);
    });

    $handlebars->registerHelper('ucwords', function ($value) {
        return ucwords($value);
    });

    $handlebars->registerHelper('toupper', function ($value) {
        return strtoupper($value);
    });

    $handlebars->registerHelper('tolower', function ($value) {
        return strtolower($value);
    });

    $handlebars->registerHelper('fileinfo', function($file, $options) {
        return $options['fn']([
            'name' => $file,
            'base' => basename($file),
            'path' => dirname($file),
            'extension' => File::getExtensionFromLink($file),
            'mime' => File::getMimeFromLink($file)
        ]);
    });

    $handlebars->registerHelper('scope', function($array, $key, $options) {
        if (isset($array[$key])) {
            $value = $array[$key];
            if(!is_array($value)) {
                $value = ['value' => $value];
            }

            return $options['fn']($value);
        }

        return $options['inverse']();
    });

    $handlebars->registerHelper('compile', function(
        $template,
        $variables,
        $options
    ) use ($handlebars) {
        $template = $handlebars->compile($template);
        return $template($variables);
    });

    $handlebars->registerHelper('partial', function(
        $name,
        $variables,
        $option
    ) use ($handlebars) {
        $partial = $handlebars->getPartial($name);
        $template = $handlebars->compile($partial);
        return $template($variables);
    });

    $handlebars->registerHelper('error_exist', function($arr, $val, $scheme, $options) {
        $name = $scheme . "_" . $val;

        if (isset($arr[$name])) {
            return $options['fn'](['value' => $arr[$name]]);
        }

        return $options['inverse']();
    });
};
