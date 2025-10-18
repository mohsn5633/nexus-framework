<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($exceptionClass ?? 'Error') ?> - Nexus Debug</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] { display: none !important; }
        .line-numbers { counter-reset: line; }
        .line-numbers .line::before {
            counter-increment: line;
            content: counter(line);
            display: inline-block;
            width: 3em;
            padding-right: 1em;
            text-align: right;
            color: #6b7280;
            border-right: 1px solid #e5e7eb;
            margin-right: 1em;
        }
        .line-error { background-color: #fee2e2; color: #991b1b; font-weight: 500; }
        .line-highlight { background-color: #fef3c7; color: #78350f; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50" x-data="{ activeTab: 'exception' }">
    <!-- Header -->
    <div class="bg-gradient-to-r from-red-600 to-pink-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <h1 class="text-3xl font-bold"><?= htmlspecialchars($exceptionClass ?? 'Exception') ?></h1>
                    </div>
                    <p class="text-xl text-red-100 font-medium"><?= htmlspecialchars($message ?? 'An error occurred') ?></p>
                    <?php if (isset($file) && isset($line)): ?>
                    <p class="text-sm text-red-200 mt-2 font-mono">
                        <?= htmlspecialchars($file) ?>:<span class="font-bold"><?= $line ?></span>
                    </p>
                    <?php endif; ?>
                </div>
                <div class="ml-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-900 text-red-100">
                        <?= htmlspecialchars($statusCode ?? 500) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex space-x-8" aria-label="Tabs">
                <button @click="activeTab = 'exception'"
                        :class="activeTab === 'exception' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Exception
                </button>
                <button @click="activeTab = 'stack'"
                        :class="activeTab === 'stack' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Stack Trace
                </button>
                <button @click="activeTab = 'request'"
                        :class="activeTab === 'request' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Request
                </button>
                <button @click="activeTab = 'environment'"
                        :class="activeTab === 'environment' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Environment
                </button>
                <button @click="activeTab = 'routes'"
                        :class="activeTab === 'routes' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Routes
                </button>
            </nav>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Exception Tab -->
        <div x-show="activeTab === 'exception'" x-cloak>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-red-50 to-pink-50 border-b border-red-100">
                    <h2 class="text-lg font-semibold text-gray-900">Error Details</h2>
                    <p class="text-sm text-gray-600 mt-1">Detailed information about the exception</p>
                </div>

                <?php if (isset($fileContent) && is_array($fileContent)): ?>
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Code Context</h3>
                    <div class="bg-gray-900 rounded-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <pre class="text-sm text-gray-100 p-4 line-numbers"><?php
                            $errorLine = $line ?? 0;
                            foreach ($fileContent as $lineNum => $lineCode):
                                $isErrorLine = ($lineNum == $errorLine);
                                $isNearError = abs($lineNum - $errorLine) <= 2;
                                $class = $isErrorLine ? 'line line-error' : ($isNearError ? 'line line-highlight' : 'line');
                            ?><code class="<?= $class ?>"><?= htmlspecialchars($lineCode) ?></code>
<?php endforeach; ?></pre>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (isset($message)): ?>
                <div class="p-6 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Exception Message</h3>
                    <p class="text-gray-900 font-mono text-sm bg-red-50 p-4 rounded-lg border border-red-200">
                        <?= htmlspecialchars($message) ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stack Trace Tab -->
        <div x-show="activeTab === 'stack'" x-cloak>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-red-50 to-pink-50 border-b border-red-100">
                    <h2 class="text-lg font-semibold text-gray-900">Stack Trace</h2>
                    <p class="text-sm text-gray-600 mt-1">Complete call stack of the exception</p>
                </div>

                <?php if (isset($stackTrace) && is_array($stackTrace)): ?>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($stackTrace as $index => $frame): ?>
                    <div class="p-6 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 text-red-600 font-semibold text-sm">
                                    <?= $index + 1 ?>
                                </span>
                            </div>
                            <div class="ml-4 flex-1">
                                <?php if (isset($frame['class'])): ?>
                                <p class="text-sm font-medium text-gray-900">
                                    <span class="text-purple-600"><?= htmlspecialchars($frame['class']) ?></span>
                                    <span class="text-gray-400"><?= htmlspecialchars($frame['type'] ?? '->') ?></span>
                                    <span class="text-blue-600"><?= htmlspecialchars($frame['function']) ?>()</span>
                                </p>
                                <?php elseif (isset($frame['function'])): ?>
                                <p class="text-sm font-medium text-blue-600">
                                    <?= htmlspecialchars($frame['function']) ?>()
                                </p>
                                <?php endif; ?>

                                <?php if (isset($frame['file'])): ?>
                                <p class="text-xs text-gray-500 mt-1 font-mono">
                                    <?= htmlspecialchars($frame['file']) ?>
                                    <?php if (isset($frame['line'])): ?>
                                    <span class="text-gray-400">:</span><span class="font-bold text-red-600"><?= $frame['line'] ?></span>
                                    <?php endif; ?>
                                </p>
                                <?php endif; ?>

                                <?php if (isset($frame['args']) && !empty($frame['args'])): ?>
                                <details class="mt-2">
                                    <summary class="text-xs text-gray-600 cursor-pointer hover:text-gray-900">
                                        <?= count($frame['args']) ?> argument<?= count($frame['args']) !== 1 ? 's' : '' ?>
                                    </summary>
                                    <pre class="mt-2 text-xs bg-gray-50 p-3 rounded border border-gray-200 overflow-x-auto"><?= htmlspecialchars(print_r($frame['args'], true)) ?></pre>
                                </details>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="p-6 text-gray-500 text-center">No stack trace available</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Request Tab -->
        <div x-show="activeTab === 'request'" x-cloak>
            <div class="space-y-6">
                <!-- Request Info -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6 bg-gradient-to-r from-red-50 to-pink-50 border-b border-red-100">
                        <h2 class="text-lg font-semibold text-gray-900">Request Information</h2>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Method</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold"><?= htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? 'N/A') ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">URL</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono break-all"><?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A') ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono"><?= htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'N/A') ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">User Agent</dt>
                                <dd class="mt-1 text-sm text-gray-900 truncate"><?= htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') ?></dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Headers -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-blue-100">
                        <h3 class="text-lg font-semibold text-gray-900">Headers</h3>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach (getallheaders() ?: [] as $key => $value): ?>
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-500"><?= htmlspecialchars($key) ?></td>
                                        <td class="px-4 py-3 text-sm text-gray-900 font-mono break-all"><?= htmlspecialchars($value) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- POST Data -->
                <?php if (!empty($_POST)): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-green-100">
                        <h3 class="text-lg font-semibold text-gray-900">POST Data</h3>
                    </div>
                    <div class="p-6">
                        <pre class="text-sm bg-gray-50 p-4 rounded-lg border border-gray-200 overflow-x-auto"><?= htmlspecialchars(json_encode($_POST, JSON_PRETTY_PRINT)) ?></pre>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Environment Tab -->
        <div x-show="activeTab === 'environment'" x-cloak>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-red-50 to-pink-50 border-b border-red-100">
                    <h2 class="text-lg font-semibold text-gray-900">Environment Variables</h2>
                    <p class="text-sm text-gray-600 mt-1">Application environment configuration</p>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="divide-y divide-gray-200">
                                <?php
                                $envVars = $_ENV;
                                ksort($envVars);
                                foreach ($envVars as $key => $value):
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-500"><?= htmlspecialchars($key) ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-900 font-mono break-all">
                                        <?php
                                        // Mask sensitive values
                                        $masked = in_array(strtoupper($key), ['PASSWORD', 'SECRET', 'KEY', 'TOKEN']) ||
                                                  strpos(strtoupper($key), 'PASSWORD') !== false ||
                                                  strpos(strtoupper($key), 'SECRET') !== false;
                                        echo $masked ? '••••••••' : htmlspecialchars($value);
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Routes Tab -->
        <div x-show="activeTab === 'routes'" x-cloak>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-red-50 to-pink-50 border-b border-red-100">
                    <h2 class="text-lg font-semibold text-gray-900">Registered Routes</h2>
                    <p class="text-sm text-gray-600 mt-1">All routes available in the application</p>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">URI</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <?php if (isset($routes) && is_array($routes)): ?>
                                    <?php foreach ($routes as $route): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <?= htmlspecialchars($route['method']) ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm font-mono text-gray-900"><?= htmlspecialchars($route['path']) ?></td>
                                        <td class="px-4 py-3 text-sm text-gray-500"><?= htmlspecialchars($route['name'] ?? '-') ?></td>
                                        <td class="px-4 py-3 text-sm text-gray-900 font-mono text-xs"><?= htmlspecialchars($route['action']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">No routes registered</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <div class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    <span class="font-semibold text-gray-900">Nexus Framework</span> v1.0.0 • PHP <?= PHP_VERSION ?>
                </div>
                <a href="/" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Go to Homepage
                </a>
            </div>
        </div>
    </div>
</body>
</html>
