<?php
require_once 'vendor/autoload.php';

echo "=== Luxid Engine Comprehensive Test ===\n\n";

// Test all classes
$testClasses = [
    // Foundation
    'Luxid\Foundation\Application' => 'Application',
    'Luxid\Foundation\Action' => 'Action',
    'Luxid\Foundation\Screen' => 'Screen',
    
    // Http
    'Luxid\Http\Request' => 'Request',
    'Luxid\Http\Response' => 'Response',
    'Luxid\Http\Session' => 'Session',
    
    // Database
    'Luxid\Database\Database' => 'Database',
    'Luxid\Database\DbEntity' => 'DbEntity',
    
    // ORM
    'Luxid\ORM\Entity' => 'Entity',
    'Luxid\ORM\UserEntity' => 'UserEntity',
    
    // Routing
    'Luxid\Routing\Router' => 'Router',
    
    // Middleware
    'Luxid\Middleware\BaseMiddleware' => 'BaseMiddleware',
    'Luxid\Middleware\AuthMiddleware' => 'AuthMiddleware',
    
    // Form
    'Luxid\Form\Form' => 'Form',
    'Luxid\Form\BaseField' => 'BaseField',
    'Luxid\Form\InputField' => 'InputField',
    'Luxid\Form\TextareaField' => 'TextareaField',
    
    // Exceptions
    'Luxid\Exceptions\NotFoundException' => 'NotFoundException',
    'Luxid\Exceptions\ForbiddenException' => 'ForbiddenException',
];

echo "Testing class loading:\n";
$allLoaded = true;
foreach ($testClasses as $class => $name) {
    if (class_exists($class)) {
        echo "  ✓ $name\n";
    } else {
        echo "  ✗ $name\n";
        $allLoaded = false;
    }
}

if ($allLoaded) {
    echo "\n✅ All classes loaded successfully!\n";
    
    // Test inheritance
    echo "\nTesting inheritance relationships:\n";
    
    // Check DbEntity extends Entity
    if (is_subclass_of('Luxid\Database\DbEntity', 'Luxid\ORM\Entity')) {
        echo "  ✓ DbEntity extends Entity\n";
    } else {
        echo "  ✗ DbEntity does not extend Entity\n";
    }
    
    // Check UserEntity extends DbEntity  
    if (is_subclass_of('Luxid\ORM\UserEntity', 'Luxid\Database\DbEntity')) {
        echo "  ✓ UserEntity extends DbEntity\n";
    } else {
        echo "  ✗ UserEntity does not extend DbEntity\n";
    }
    
    // Check AuthMiddleware extends BaseMiddleware
    if (is_subclass_of('Luxid\Middleware\AuthMiddleware', 'Luxid\Middleware\BaseMiddleware')) {
        echo "  ✓ AuthMiddleware extends BaseMiddleware\n";
    } else {
        echo "  ✗ AuthMiddleware does not extend BaseMiddleware\n";
    }
    
    // Check Exceptions extend Exception
    if (is_subclass_of('Luxid\Exceptions\NotFoundException', 'Exception')) {
        echo "  ✓ NotFoundException extends Exception\n";
    } else {
        echo "  ✗ NotFoundException does not extend Exception\n";
    }
    
    if (is_subclass_of('Luxid\Exceptions\ForbiddenException', 'Exception')) {
        echo "  ✓ ForbiddenException extends Exception\n";
    } else {
        echo "  ✗ ForbiddenException does not extend Exception\n";
    }
    
    // Check Form classes
    if (is_subclass_of('Luxid\Form\InputField', 'Luxid\Form\BaseField')) {
        echo "  ✓ InputField extends BaseField\n";
    } else {
        echo "  ✗ InputField does not extend BaseField\n";
    }
    
    if (is_subclass_of('Luxid\Form\TextareaField', 'Luxid\Form\BaseField')) {
        echo "  ✓ TextareaField extends BaseField\n";
    } else {
        echo "  ✗ TextareaField does not extend BaseField\n";
    }
    
} else {
    echo "\n❌ Some classes failed to load\n";
}

// Test autoloader can instantiate (without actual dependencies)
echo "\nTesting instantiation (where possible):\n";
try {
    $request = new Luxid\Http\Request();
    echo "  ✓ Request instantiated\n";
} catch (Exception $e) {
    echo "  - Request: " . $e->getMessage() . "\n";
}

try {
    $response = new Luxid\Http\Response();
    echo "  ✓ Response instantiated\n";
} catch (Exception $e) {
    echo "  - Response: " . $e->getMessage() . "\n";
}

try {
    $router = new Luxid\Routing\Router(new Luxid\Http\Request(), new Luxid\Http\Response());
    echo "  ✓ Router instantiated\n";
} catch (Exception $e) {
    echo "  - Router: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
