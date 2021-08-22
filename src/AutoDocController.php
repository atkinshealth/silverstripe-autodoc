<?php

namespace AtkinsHealth\AutoDoc;

use Codeshift\CodemodRunner;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Manifest\ModuleLoader;

class AutoDocController extends Controller
{
    private static $url_handlers = [
        '' => 'autodoc'
    ];

    private static $allowed_actions = [
        'autodoc'
    ];

    public function autodoc(HTTPRequest $request)
    {
        $module = $request->getVar('module') ?: self::config()->get('default_module');

        $module = ModuleLoader::getModule($module);
        $workPath = $module->getPath();

        $codemodPath = __DIR__ . '/Codemod.php';

        $runner = new CodemodRunner();
        $runner->addCodemod($codemodPath);
        $runner->execute(
            $workPath,
            $workPath
        );
    }
}
