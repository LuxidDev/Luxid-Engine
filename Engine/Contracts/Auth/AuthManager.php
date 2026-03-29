<?php

namespace Luxid\Contracts\Auth;

interface AuthManager
{
  public function guard(?string $name = null): Guard;
  public function shouldUse(string $name): self;
  public function user(): ?Authenticatable;
  public function check(): bool;
  public function attempt(array $credentials = [], bool $remember = false): bool;
  public function login(Authenticatable $user, bool $remember = false): bool;
  public function logout(): void;
}
