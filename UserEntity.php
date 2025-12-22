<?php

namespace engine\system;

abstract class UserEntity extends DbEntity
{
    abstract public function getDisplayName(): string;
}
