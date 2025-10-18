<?php

namespace Nexus\View;

class ViewCompiler
{
    protected string $cachePath;
    protected array $extensions = [];
    protected array $sections = [];
    protected array $sectionStack = [];
    protected ?string $extends = null;

    public function __construct(string $cachePath)
    {
        $this->cachePath = $cachePath;
        $this->ensureCacheDirectoryExists();
    }

    /**
     * Compile and render a view
     */
    public function compile(string $viewPath, array $data = []): string
    {
        $compiled = $this->getCompiledPath($viewPath);

        // Compile if needed
        if (!file_exists($compiled) || filemtime($viewPath) > filemtime($compiled)) {
            $contents = file_get_contents($viewPath);
            $compiled = $this->compileString($contents);
            $this->saveCompiled($viewPath, $compiled);
        }

        return $this->render($this->getCompiledPath($viewPath), $data);
    }

    /**
     * Compile a template string
     */
    public function compileString(string $template): string
    {
        // Compile comments
        $template = $this->compileComments($template);

        // Compile PHP blocks
        $template = $this->compilePhp($template);

        // Compile echo statements
        $template = $this->compileEchos($template);

        // Compile directives
        $template = $this->compileExtends($template);
        $template = $this->compileSections($template);
        $template = $this->compileYields($template);
        $template = $this->compileIncludes($template);
        $template = $this->compileConditionals($template);
        $template = $this->compileLoops($template);
        $template = $this->compileCsrf($template);
        $template = $this->compileMethod($template);
        $template = $this->compileAuth($template);
        $template = $this->compileJson($template);

        return $template;
    }

    /**
     * Compile Blade comments
     */
    protected function compileComments(string $template): string
    {
        return preg_replace('/{{--((.|\s)*?)--}}/', '', $template);
    }

    /**
     * Compile PHP blocks
     */
    protected function compilePhp(string $template): string
    {
        return preg_replace('/@php\s*(.+?)\s*@endphp/s', '<?php $1 ?>', $template);
    }

    /**
     * Compile echo statements
     */
    protected function compileEchos(string $template): string
    {
        // Escaped echoes {{ }}
        $template = preg_replace('/{{{\s*(.+?)\s*}}}/', '<?php echo e($1); ?>', $template);
        $template = preg_replace('/{{\s*(.+?)\s*}}/', '<?php echo e($1); ?>', $template);

        // Raw echoes {!! !!}
        $template = preg_replace('/{!!\s*(.+?)\s*!!}/', '<?php echo $1; ?>', $template);

        return $template;
    }

    /**
     * Compile @extends directive
     */
    protected function compileExtends(string $template): string
    {
        return preg_replace('/@extends\([\'"](.+?)[\'"]\)/', '<?php $__extends = "$1"; ?>', $template);
    }

    /**
     * Compile @section and @endsection
     */
    protected function compileSections(string $template): string
    {
        // @section('name')
        $template = preg_replace('/@section\([\'"](.+?)[\'"]\)/', '<?php $__currentSection = "$1"; ob_start(); ?>', $template);

        // @endsection
        $template = preg_replace('/@endsection/', '<?php $__sections[$__currentSection] = ob_get_clean(); ?>', $template);

        return $template;
    }

    /**
     * Compile @yield directive
     */
    protected function compileYields(string $template): string
    {
        return preg_replace('/@yield\([\'"](.+?)[\'"](,\s*[\'"](.+?)[\'"])?\)/', '<?php echo $__sections["$1"] ?? "$3"; ?>', $template);
    }

    /**
     * Compile @include directive
     */
    protected function compileIncludes(string $template): string
    {
        return preg_replace_callback('/@include\([\'"](.+?)[\'"](,\s*(\[.+?\]))?\)/', function ($matches) {
            $view = $matches[1];
            $data = $matches[3] ?? '[]';
            return '<?php echo \\Nexus\\View\\View::make("' . $view . '", array_merge(get_defined_vars(), ' . $data . ')); ?>';
        }, $template);
    }

    /**
     * Compile conditional directives
     */
    protected function compileConditionals(string $template): string
    {
        // @if - match content within parentheses, handling nested parens
        $template = preg_replace_callback('/@if\s*\(((?:[^()]+|\((?:[^()]+|\([^()]*\))*\))*)\)/', function($matches) {
            return '<?php if(' . $matches[1] . '): ?>';
        }, $template);

        // @elseif
        $template = preg_replace_callback('/@elseif\s*\(((?:[^()]+|\((?:[^()]+|\([^()]*\))*\))*)\)/', function($matches) {
            return '<?php elseif(' . $matches[1] . '): ?>';
        }, $template);

        // @else
        $template = preg_replace('/@else/', '<?php else: ?>', $template);

        // @endif
        $template = preg_replace('/@endif/', '<?php endif; ?>', $template);

        // @unless
        $template = preg_replace_callback('/@unless\s*\(((?:[^()]+|\((?:[^()]+|\([^()]*\))*\))*)\)/', function($matches) {
            return '<?php if(!(' . $matches[1] . ')): ?>';
        }, $template);

        // @endunless
        $template = preg_replace('/@endunless/', '<?php endif; ?>', $template);

        // @isset
        $template = preg_replace_callback('/@isset\s*\(((?:[^()]+|\((?:[^()]+|\([^()]*\))*\))*)\)/', function($matches) {
            return '<?php if(isset(' . $matches[1] . ')): ?>';
        }, $template);

        // @endisset
        $template = preg_replace('/@endisset/', '<?php endif; ?>', $template);

        // @empty
        $template = preg_replace_callback('/@empty\s*\(((?:[^()]+|\((?:[^()]+|\([^()]*\))*\))*)\)/', function($matches) {
            return '<?php if(empty(' . $matches[1] . ')): ?>';
        }, $template);

        // @endempty
        $template = preg_replace('/@endempty/', '<?php endif; ?>', $template);

        return $template;
    }

    /**
     * Compile loop directives
     */
    protected function compileLoops(string $template): string
    {
        // @foreach
        $template = preg_replace('/@foreach\s*\((.+?)\)/', '<?php foreach($1): ?>', $template);

        // @endforeach
        $template = preg_replace('/@endforeach/', '<?php endforeach; ?>', $template);

        // @for
        $template = preg_replace('/@for\s*\((.+?)\)/', '<?php for($1): ?>', $template);

        // @endfor
        $template = preg_replace('/@endfor/', '<?php endfor; ?>', $template);

        // @while
        $template = preg_replace('/@while\s*\((.+?)\)/', '<?php while($1): ?>', $template);

        // @endwhile
        $template = preg_replace('/@endwhile/', '<?php endwhile; ?>', $template);

        // @forelse
        $template = preg_replace('/@forelse\s*\((.+?)\)/', '<?php $__empty = true; foreach($1): $__empty = false; ?>', $template);

        // @empty (within forelse)
        $template = preg_replace('/@empty/', '<?php endforeach; if($__empty): ?>', $template);

        // @endforelse
        $template = preg_replace('/@endforelse/', '<?php endif; ?>', $template);

        return $template;
    }

    /**
     * Compile @csrf directive
     */
    protected function compileCsrf(string $template): string
    {
        return preg_replace('/@csrf/', '<?php echo \'<input type="hidden" name="_token" value="\' . csrf_token() . \'">\'; ?>', $template);
    }

    /**
     * Compile @method directive
     */
    protected function compileMethod(string $template): string
    {
        return preg_replace('/@method\([\'"](.+?)[\'"]\)/', '<?php echo \'<input type="hidden" name="_method" value="$1">\'; ?>', $template);
    }

    /**
     * Compile @auth directive
     */
    protected function compileAuth(string $template): string
    {
        // @auth
        $template = preg_replace('/@auth/', '<?php if(auth()->check()): ?>', $template);

        // @endauth
        $template = preg_replace('/@endauth/', '<?php endif; ?>', $template);

        // @guest
        $template = preg_replace('/@guest/', '<?php if(!auth()->check()): ?>', $template);

        // @endguest
        $template = preg_replace('/@endguest/', '<?php endif; ?>', $template);

        return $template;
    }

    /**
     * Compile @json directive
     */
    protected function compileJson(string $template): string
    {
        return preg_replace('/@json\((.+?)\)/', '<?php echo json_encode($1); ?>', $template);
    }

    /**
     * Render a compiled view
     */
    protected function render(string $compiled, array $data): string
    {
        $__sections = [];
        $__extends = null;

        extract($data);

        ob_start();
        include $compiled;
        $content = ob_get_clean();

        // If extends a layout, render the layout
        if ($__extends) {
            $layoutContent = View::make($__extends, array_merge($data, ['__sections' => $__sections]));
            return $layoutContent;
        }

        return $content;
    }

    /**
     * Get the compiled file path
     */
    protected function getCompiledPath(string $viewPath): string
    {
        return $this->cachePath . '/' . md5($viewPath) . '.php';
    }

    /**
     * Save compiled template
     */
    protected function saveCompiled(string $viewPath, string $compiled): void
    {
        $path = $this->getCompiledPath($viewPath);
        file_put_contents($path, $compiled);
    }

    /**
     * Ensure cache directory exists
     */
    protected function ensureCacheDirectoryExists(): void
    {
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Clear the cache
     */
    public function clearCache(): void
    {
        $files = glob($this->cachePath . '/*.php');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
