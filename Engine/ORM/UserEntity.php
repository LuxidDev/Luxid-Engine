<?php

namespace Luxid\ORM;

use Rocket\ORM\Entity;

/**
 * Base User Entity for Luxid applications
 * 
 * This class extends Rocket's Entity and provides user-specific functionality.
 * Your User entity should extend this class.
 */
abstract class UserEntity extends Entity
{
  /**
   * Get the user's display name
   */
  abstract public function getDisplayName(): string;

  /**
   * Get the password field name (can be overridden if different)
   */
  protected function getPasswordField(): string
  {
    return 'password';
  }

  /**
   * Check if user is authenticated
   */
  public function isAuthenticated(): bool
  {
    // Check if id property exists and is > 0
    return property_exists($this, 'id') && $this->id > 0;
  }

  /**
   * Get the password value
   */
  protected function getPassword(): ?string
  {
    $field = $this->getPasswordField();
    return property_exists($this, $field) ? $this->{$field} : null;
  }

  /**
   * Set the password value
   */
  protected function setPassword(string $password): void
  {
    $field = $this->getPasswordField();
    if (property_exists($this, $field)) {
      $this->{$field} = $password;
    }
  }

  /**
   * Verify password against stored hash
   */
  public function verifyPassword(string $password): bool
  {
    $storedHash = $this->getPassword();
    if (empty($storedHash)) {
      return false;
    }

    return password_verify($password, $storedHash);
  }

  /**
   * Check if password needs rehashing
   */
  public function needsRehash(): bool
  {
    $storedHash = $this->getPassword();
    if (empty($storedHash)) {
      return false;
    }

    return password_needs_rehash($storedHash, PASSWORD_DEFAULT);
  }

  /**
   * Hash password if not already hashed
   * Call this in beforeSave() hook
   */
  protected function hashPassword(): void
  {
    $password = $this->getPassword();
    if (empty($password)) {
      return;
    }

    // Check if already hashed (starts with $2y$)
    if (strpos($password, '$2y$') !== 0) {
      $this->setPassword(password_hash($password, PASSWORD_DEFAULT));
    }
  }

  /**
   * Find user by email
   */
  public static function findByEmail(string $email): ?self
  {
    return self::findOne(['email' => $email]);
  }

  /**
   * Override beforeSave to automatically hash password
   */
  protected function beforeSave(): void
  {
    parent::beforeSave();
    $this->hashPassword();
  }
}
