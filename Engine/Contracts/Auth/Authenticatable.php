<?php

namespace Luxid\Contracts\Auth;

interface Authenticatable
{
  public function getAuthIdentifierName(): string;
  public function getAuthIdentifier();
  public function getAuthPassword(): string;
  public function getAuthPasswordName(): string;
  public function getRememberToken(): ?string;
  public function setRememberToken(?string $value): void;
  public function getRememberTokenName(): string;
}
