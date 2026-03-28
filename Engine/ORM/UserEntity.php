<?php

namespace Luxid\ORM;

use Rocket\ORM\Entity;

abstract class UserEntity extends Entity
{
  abstract public function getDisplayName(): string;

  /**
   * Check if user is authenticated
   */
  public function isAuthenticated(): bool
  {
    // Check if id property exists and is > 0
    return property_exists($this, 'id') && $this->id > 0;
  }

  /**
   * Verify password against stored hash
   */
  public function verifyPassword(string $password): bool
  {
    // Check if password property exists
    if (!property_exists($this, 'password')) {
      return false;
    }

    return password_verify($password, $this->password);
  }

  /**
   * Hash password if not already hashed
   */
  protected function hashPassword(): void
  {
    if (property_exists($this, 'password') && !empty($this->password)) {
      // Check if already hashed (starts with $2y$)
      if (strpos($this->password, '$2y$') !== 0) {
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
      }
    }
  }
}
