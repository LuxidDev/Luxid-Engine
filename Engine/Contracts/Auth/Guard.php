<?php

namespace Luxid\Contracts\Auth;

interface Guard
{
  public function check(): bool;
  public function guest(): bool;
  public function user(): ?Authenticatable;
  public function id();
  public function validate(array $credentials = []): bool;
  public function attempt(array $credentials = [], bool $remember = false): bool;
  public function login(Authenticatable $user, bool $remember = false): bool;
  public function logout(): void;
}
