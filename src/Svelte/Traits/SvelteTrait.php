<?php

namespace ClassKit\Svelte\Traits;

trait SvelteTrait
{
    public function encodeArrayForSvelte(array $arr): string
    {
        return htmlspecialchars(json_encode($arr, JSON_THROW_ON_ERROR), ENT_QUOTES, 'UTF-8');
    }

    public function getSvelteJavascriptPath(string $pkgHandle, string $classHandle, string $callerFunction, string $file): string
    {
        return REL_DIR_PACKAGES . '/' . $pkgHandle . '/elements/frontend/' . $classHandle . '/' . $callerFunction . '/' . $file;
    }

    public function getSvelteJavascript(string $pkgHandle, string $file)
    {
        $callerFile = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file']
        ?? throw new \LogicException('Unable to determine the calling file.');
        $classHandle = pathinfo($callerFile, PATHINFO_FILENAME);

        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1] ?? null;
        $callerFunction = $caller['function']
        ?? throw new \LogicException('Unable to determine the calling function.');

        /**
         * @var \Concrete\Core\Page\Controller\PageController|\ClassKit\Svelte\Traits\SvelteTrait $this
         */
        $this->addFooterItem('<script type="module" src="' . $this->getSvelteJavascriptPath($pkgHandle, $classHandle, $callerFunction, $file) . '"></script>');
    }
}
