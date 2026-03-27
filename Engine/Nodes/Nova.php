<?php

namespace Luxid\Nodes;

use Luxid\Foundation\Application;
use Luxid\Nova\Slot;

class Nova
{
  /**
   * Get the current Screen instance (legacy)
   */
  protected static function instance()
  {
    if (!Application::$app || !Application::$app->screen) {
      throw new \RuntimeException("Nova/Screen instance is not available.");
    }

    return Application::$app->screen;
  }

  /**
   * Check if luxid/nova is available
   */
  protected static function hasNovaPackage(): bool
  {
    return class_exists('Luxid\Nova\ComponentManager');
  }

  /**
   * Check if a component exists
   */
  protected static function isNovaComponent(string $name, string $type = 'pages'): bool
  {
    if (!self::hasNovaPackage()) {
      return false;
    }

    // Convert dot notation to path
    $path = str_replace('.', '/', $name);
    $componentName = $type . '/' . $path;

    return \Luxid\Nova\ComponentManager::has($componentName);
  }

  /**
   * Render a Nova component
   */
  protected static function renderNovaComponent(string $name, array $data = [], string $type = 'pages'): string
  {
    $path = str_replace('.', '/', $name);
    $componentName = $type . '/' . $path;

    return \nova($componentName, $data);
  }

  /**
   * Render a screen or component with optional layout
   * 
   * This method intelligently chooses between:
   * - New reactive Nova components (if luxid/nova is installed)
   * - Legacy static screens (fallback)
   * 
   * Examples:
   * - Nova::render('Welcome') -> renders pages/Welcome with default layout
   * - Nova::render('Welcome', layout: 'AuthLayout') -> renders with custom layout
   * - Nova::render('Button', type: 'components') -> renders component without layout
   */
  public static function render(string $screen, array $data = [], string $type = 'pages', ?string $layout = null): string
  {
    // Try to use luxid/nova if available
    if (self::hasNovaPackage()) {
      // If no layout specified and it's a page, use default layout
      if ($type === 'pages' && $layout === null) {
        $config = self::getConfig();
        $layout = $config['default_layout'] ?? 'AppLayout';
      }

      // If we have a layout to apply
      if ($layout !== null && $type === 'pages') {
        // Capture the page content for the layout's slot
        Slot::start('content');
        echo self::renderNovaComponent($screen, $data, $type);
        Slot::end();

        // Render the layout with the captured content
        return self::renderNovaComponent($layout, $data, 'layouts');
      }

      // Render component directly (no layout)
      return self::renderNovaComponent($screen, $data, $type);
    }

    // Fall back to legacy static screens
    return self::instance()->renderScreen($screen, $data);
  }

  /**
   * Render just content without frame
   */
  public static function content(string $screenContent): string
  {
    // If it's a Nova component name
    if (self::isNovaComponent($screenContent, 'pages')) {
      return self::renderNovaComponent($screenContent, [], 'pages');
    }

    // Legacy content rendering
    return self::instance()->renderContent($screenContent);
  }

  /**
   * Get Nova configuration from nova.json
   */
  private static function getConfig(): array
  {
    $configFile = Application::$ROOT_DIR . '/nova/nova.json';
    if (file_exists($configFile)) {
      $config = json_decode(file_get_contents($configFile), true);
      return is_array($config) ? $config : [];
    }
    return [];
  }

  /**
   * Check if a component exists
   */
  public static function exists(string $name, string $type = 'pages'): bool
  {
    return self::isNovaComponent($name, $type);
  }
}
