<?php
require_once 'vendor/autoload.php';

echo "Quick Luxid Engine Test\n\n";

// Test the problematic class first
try {
    if (class_exists('Luxid\Database\DbEntity')) {
        echo "✓ DbEntity class loaded\n";
        
        $reflection = new ReflectionClass('Luxid\Database\DbEntity');
        echo "✓ Namespace: " . $reflection->getNamespaceName() . "\n";
        
        if ($reflection->getParentClass()) {
            echo "✓ Extends: " . $reflection->getParentClass()->getName() . "\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// Test a few more
$classes = [
    'Luxid\Foundation\Application',
    'Luxid\ORM\Entity', 
    'Luxid\Http\Request',
    'Luxid\Routing\Router',
    'Luxid\Middleware\AuthMiddleware'
];

echo "\nTesting other classes:\n";
foreach ($classes as $class) {
    echo (class_exists($class) ? "  ✓ " : "  ✗ ") . "$class\n";
}

echo "\n=== Checking for issues ===\n";
// Check for old namespace
exec("grep -r 'engine\\\\\\\\system' Engine/ --include='*.php' 2>/dev/null", $output, $return);
if ($return == 1) {
    echo "✓ No engine\\system references found\n";
} else {
    echo "✗ Found old references\n";
    print_r($output);
}
